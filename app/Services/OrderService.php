<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Jobs\SendOrderConfirmation;

class OrderService
{
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::firstOrCreate(
                ['email' => $data['customer']['email']],
                ['name' => $data['customer']['name']]
            );

            // Lock products in a consistent order to prevent
            // concurrent orders from overselling stock.
            $productIds = collect($data['products'])
                ->pluck('product_id')
                ->unique()
                ->sort()
                ->values();

            $products = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $quantities = collect($data['products'])
                ->groupBy('product_id')
                ->map(fn ($items) => $items->sum('quantity'));

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);

                if (!$product || $product->stock_on_hand < $quantity) {
                    throw ValidationException::withMessages([
                        'products' => [
                            'Insufficient stock for product ID '
                            . $productId . '.',
                        ],
                    ]);
                }
            }

            $subtotal = 0;
            $taxTotal = 0;

            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => 0,
                'tax_total' => 0,
                'grand_total' => 0,
            ]);

            foreach ($data['products'] as $item) {
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];

                $lineSubtotal = round(
                    (float) $product->price * $quantity,
                    2
                );

                $lineTax = round(
                    $lineSubtotal * (float) $product->tax_percentage / 100,
                    2
                );

                $subtotal += $lineSubtotal;
                $taxTotal += $lineTax;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'tax_percentage' => $product->tax_percentage,
                ]);
            }

            foreach ($quantities as $productId => $quantity) {
                $products->get($productId)->decrement(
                    'stock_on_hand',
                    $quantity
                );
            }

            $order->update([
                'subtotal' => round($subtotal, 2),
                'tax_total' => round($taxTotal, 2),
                'grand_total' => round($subtotal + $taxTotal, 2),
            ]);

            $order->load(['customer', 'items.product']);
            DB::afterCommit(function () use ($order) {
                SendOrderConfirmation::dispatch($order);
            });

            return $order;
        });
    }
}
