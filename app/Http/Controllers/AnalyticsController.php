<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends BaseApiController
{
    public function restaurantTrends(Request $req)
    {
        $validated = $req->validate([
            'restaurant_ids' => 'nullable|array',
            'restaurant_ids.*' => 'exists:restaurants,id',
            'from' => 'nullable|date|before_or_equal:to',
            'to' => 'nullable|date|after_or_equal:from',
            'minA' => 'nullable|numeric|min:0',
            'maxA' => 'nullable|numeric|gte:minA',
            'hFrom' => 'nullable|date_format:H:i|before_or_equal:hTo',
            'hTo' => 'nullable|date_format:H:i|after_or_equal:hFrom',
        ]);

        $restaurantIds = $validated['restaurant_ids'] ?? [];
        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : null;
        $to = isset($validated['to']) ? Carbon::parse($validated['to']) : null;
        $minA = $validated['minA'] ?? 0;
        $maxA = $validated['maxA'] ?? 0;
        $hFrom = $validated['hFrom'] ?? null;
        $hTo = $validated['hTo'] ?? null;

        // If no restaurant_ids provided or empty array, get all restaurants
        if (empty($restaurantIds)) {
            $restaurants = Restaurant::all();
        } else {
            // Get specific restaurants by IDs
            $restaurants = Restaurant::whereIn('id', $restaurantIds)->get();
        }

        // Create cache key based on all parameters
        $cacheKey = sprintf(
            'restaurant_trends:%s:%s:%s:%f:%f:%s:%s',
            empty($restaurantIds) ? 'all' : implode(',', $restaurantIds),
            $from ? $from->format('Y-m-d') : 'all',
            $to ? $to->format('Y-m-d') : 'all',
            $minA,
            $maxA,
            $hFrom ?: 'all',
            $hTo ?: 'all'
        );

        // Check if data is cached
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            return $this->success($cachedData, 'Restaurants trends fetched successfully (cached)');
        }

        // Generate data and cache it
        return Cache::remember($cacheKey, 60, function() use($restaurants, $from, $to, $minA, $maxA, $hFrom, $hTo) {
            $allTrends = [];

            foreach ($restaurants as $restaurant) {
                $trends = $this->getTrendsData($restaurant, $from, $to, $minA, $maxA, $hFrom, $hTo);
                $allTrends[] = [
                    'restaurant_id' => $restaurant->id,
                    'restaurant_name' => $restaurant->name,
                    'trends' => $trends
                ];
            }

            return $allTrends;
        });
    }

    private function getTrendsData($restaurant, $from, $to, $minA, $maxA, $hFrom, $hTo)
    {
        $orders = Order::where('restaurant_id', $restaurant->id)
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('order_time', [$from, $to]);
            })
            ->when($minA > 0, function ($query) use ($minA) {
                $query->where('order_amount', '>=', $minA);
            })
            ->when($maxA > 0, function ($query) use ($maxA) {
                $query->where('order_amount', '<=', $maxA);
            })
            ->when($hFrom, function ($query) use ($hFrom) {
                $query->whereRaw('EXTRACT(TIME FROM order_time) >= ?', [$hFrom]);
            })
            ->when($hTo, function ($query) use ($hTo) {
                $query->whereRaw('EXTRACT(TIME FROM order_time) <= ?', [$hTo]);
            });

        // daily counts, order_amount_sum, average
        $dailyData = (clone $orders)
            ->select(
                DB::raw('DATE(order_time) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('ROUND(SUM(order_amount)::numeric, 2) as amount_sum'),
                DB::raw('ROUND(AVG(order_amount)::numeric, 2) as average')
            )
            ->groupBy('date')
            ->get();



        // hourly counts, order_amount_sum, average
        $hourlyData = (clone $orders)
            ->select(
                DB::raw('TO_CHAR(order_time, \'YYYY-MM-DD HH24:00\') as hour'),
                DB::raw('COUNT(*) as count'),
                DB::raw('ROUND(SUM(order_amount)::numeric, 2) as amount_sum'),
                DB::raw('ROUND(AVG(order_amount)::numeric, 2) as average')
            )
            ->groupBy('hour')
            ->get();

        return [
            'daily' => $dailyData,
            'hourly' => $hourlyData
        ];
    }

    public function topRestaurants(Request $req)
    {
        $validated = $req->validate([
            'restaurant_ids' => 'nullable|array',
            'restaurant_ids.*' => 'exists:restaurants,id',
            'from' => 'nullable|date|before_or_equal:to',
            'to' => 'nullable|date|after_or_equal:from',
            'minA' => 'nullable|numeric|min:0',
            'maxA' => 'nullable|numeric|gte:minA',
        ]);

        $restaurantIds = $validated['restaurant_ids'] ?? [];
        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : null;
        $to = isset($validated['to']) ? Carbon::parse($validated['to']) : null;
        $minA = $validated['minA'] ?? 0;
        $maxA = $validated['maxA'] ?? 0;

        $cacheKey = sprintf('top_restaurants:%s:%s:%s:%f:%f', empty($restaurantIds) ? 'all' : implode(',', $restaurantIds), $from ? $from->format('Y-m-d') : 'all', $to ? $to->format('Y-m-d') : 'all', $minA, $maxA);

        $cachedData = Cache::get($cacheKey);

        if ($cachedData) {
            return $this->success($cachedData, 'Top restaurants fetched successfully');
        }

        return Cache::remember($cacheKey, 60, function() use($from, $to, $restaurantIds, $minA, $maxA) {
            $restaurants = Restaurant::when(!empty($restaurantIds), function($query) use($restaurantIds) {
                $query->whereIn('id', $restaurantIds);
            })
            ->withCount(['orders' => function($query) use($from, $to, $minA, $maxA) {
                if ($from && $to) {
                    $query->whereBetween('order_time', [$from, $to]);
                }
                if ($minA > 0) {
                    $query->where('order_amount', '>=', $minA);
                }
                if ($maxA > 0) {
                    $query->where('order_amount', '<=', $maxA);
                }
            }])
            ->withSum(['orders' => function($query) use($from, $to, $minA, $maxA) {
                if ($from && $to) {
                    $query->whereBetween('order_time', [$from, $to]);
                }
                if ($minA > 0) {
                    $query->where('order_amount', '>=', $minA);
                }
                if ($maxA > 0) {
                    $query->where('order_amount', '<=', $maxA);
                }
            }], 'order_amount')
            ->orderBy('orders_sum_order_amount', 'desc')
            ->limit(3)
            ->get();

            return $restaurants;
        });
    }
}
