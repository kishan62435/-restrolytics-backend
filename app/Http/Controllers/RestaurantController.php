<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
// use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class RestaurantController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {

        $validated = $req->validate([
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:name,location,orders,order_amount,created_at',
            'dir' => 'nullable|string|in:asc,desc',
            'from' => 'nullable|date|before_or_equal:to',
            'to' => 'nullable|date|after_or_equal:from',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // $validated = $req->validate([
        //     'dateRange' => 'nullable|string',
        //     'restaurant' => 'nullable|string|max:255',
        //     'amountRange' => 'nullable|string',
        //     'hourRange' => 'nullable|string',
        //     'search' => 'nullable|string|max:255',
        //     'sort' => 'nullable|string|in:name,location,orders,order_amount,created_at',
        //     'dir' => 'nullable|string|in:asc,desc',
        //     'per_page' => 'nullable|integer|min:1|max:100',
        // ]);





        $search = $validated['search']?? null;
        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;
        $sort = $validated['sort'] ?? 'name';
        $dir = $validated['dir'] ?? 'asc';
        $perPage = $validated['per_page'] ?? 10;

        $restaurants = Restaurant::withCount(['orders' => function ($query) use ($from, $to) {
            if ($from && $to) {
                $query->whereBetween('order_time', [$from, $to]);
            }
        }])
            ->withSum(['orders' => function ($query) use ($from, $to) {
                if ($from && $to) {
                    $query->whereBetween('order_time', [$from, $to]);
                }
            }], 'order_amount')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('location', 'like', "%$search%");
                });
            })
            ->when(in_array($sort, ['orders', 'order_amount']), function ($query) use ($sort, $dir) {
                $orderBy = $sort === 'orders' ? 'orders_count' : 'orders_sum_order_amount';
                $query->orderBy($orderBy, $dir);
            })
            ->when(!in_array($sort, ['orders', 'order_amount']), function ($query) use ($sort, $dir) {
                $query->orderBy($sort, $dir);
            })
            ->paginate($perPage);

        return $this->success($restaurants, 'Restaurants fetched successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Restaurant $restaurant)
    {
        // return $restaurant;
        return $this->success($restaurant, 'Restaurant fetched successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant)
    {
        //
    }
}
