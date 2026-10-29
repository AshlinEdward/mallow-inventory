<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $customer = Customer::where(
            'email',
            $validated['email']
        )->first();

        if (!$customer) {
            return response()->json([
                'message' => 'Customer not found.',
            ], 404);
        }

        $orders = $customer->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return response()->json([
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
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
                    'created_at' => $order->created_at,
                ];
            }),
        ]);
    }

    
    public function customers(): JsonResponse
    {
        $customers = Customer::query()
            ->withCount('orders')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json([
            'customers' => $customers,
        ]);
    }

}
