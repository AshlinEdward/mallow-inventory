<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_an_order_successfully(): void
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'tax_percentage' => 18.00,
            'stock_on_hand' => 5,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer' => [
                'name' => 'Test Customer',
                'email' => 'test@example.com',
            ],
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Order created successfully.')
            ->assertJsonPath('data.subtotal', '200.00')
            ->assertJsonPath('data.tax_total', '36.00')
            ->assertJsonPath('data.grand_total', '236.00');

        $this->assertDatabaseHas('customers', [
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('orders', [
            'subtotal' => 200.00,
            'tax_total' => 36.00,
            'grand_total' => 236.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_on_hand' => 3,
        ]);
    }

    
    public function test_order_is_rejected_when_stock_is_insufficient(): void
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'tax_percentage' => 18.00,
            'stock_on_hand' => 1,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer' => [
                'name' => 'Stock Test Customer',
                'email' => 'stocktest@example.com',
            ],
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('products');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_on_hand' => 1,
        ]);
    }

    
    public function test_stock_cannot_be_oversold_across_orders(): void
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'tax_percentage' => 0,
            'stock_on_hand' => 1,
        ]);

        $firstOrder = [
            'customer' => [
                'name' => 'First Customer',
                'email' => 'first@example.com',
            ],
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $secondOrder = [
            'customer' => [
                'name' => 'Second Customer',
                'email' => 'second@example.com',
            ],
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $this->postJson('/api/orders', $firstOrder)
            ->assertCreated();

        $this->postJson('/api/orders', $secondOrder)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('products');

        $this->assertDatabaseCount('orders', 1);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_on_hand' => 0,
        ]);
    }


}
