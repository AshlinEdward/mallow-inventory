<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function store(
        StoreOrderRequest $request,
        OrderService $orderService
    ): JsonResponse {
        $order = $orderService->createOrder($request->validated());

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => [
                'id' => $order->id,
                'customer' => [
                    'name' => $order->customer->name,
                    'email' => $order->customer->email,
                ],
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_percentage' => $item->tax_percentage,
                    ];
                }),
                'subtotal' => $order->subtotal,
                'tax_total' => $order->tax_total,
                'grand_total' => $order->grand_total,
            ],
        ], 201);
    }

    
    public function index(): JsonResponse
    {
        $orders = \App\Models\Order::with([
            'customer',
            'items.product',
        ])->latest()->get();

        return response()->json([
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer' => [
                        'name' => $order->customer->name,
                        'email' => $order->customer->email,
                    ],
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'tax_percentage' => $item->tax_percentage,
                        ];
                    })->values(),
                    'subtotal' => $order->subtotal,
                    'tax_total' => $order->tax_total,
                    'grand_total' => $order->grand_total,
                    'created_at' => $order->created_at,
                ];
            })->values(),
        ]);
    }

}
