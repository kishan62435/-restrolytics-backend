<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'from' => 'nullable|date|before_or_equal:to',
            'to' => 'nullable|date|after_or_equal:from',
            'minA' => 'nullable|numeric|min:0',
            'maxA' => 'nullable|numeric|gte:minA',
            'hFrom' => 'nullable|date_format:H:i|before_or_equal:hTo',
            'hTo' => 'nullable|date_format:H:i|after_or_equal:hFrom',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $restaurantId = $validated['restaurant_id'];
        $from = isset($validated['from']) ? $validated['from'] : null;
        $to = isset($validated['to']) ? $validated['to'] : null;
        $minA = $validated['minA'] ?? null;
        $maxA = $validated['maxA'] ?? null;
        $hFrom = $validated['hFrom'] ?? null;
        $hTo = $validated['hTo'] ?? null;
        $perPage = $validated['per_page'] ?? 10;

        $query = Order::where('restaurant_id', $restaurantId)
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('order_time', [$from, $to]);
            })
            ->when($minA !== null, function ($query) use ($minA) {
                $query->where('order_amount', '>=', $minA);
            })
            ->when($maxA !== null, function ($query) use ($maxA) {
                $query->where('order_amount', '<=', $maxA);
            })
            ->when($hFrom, function ($query) use ($hFrom) {
                $query->whereRaw('EXTRACT(TIME FROM order_time) >= ?', [$hFrom]);
            })
            ->when($hTo, function ($query) use ($hTo) {
                $query->whereRaw('EXTRACT(TIME FROM order_time) <= ?', [$hTo]);
            })
            ->orderBy('order_time', 'desc');

        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ]
        ]);
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
    public function show(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
