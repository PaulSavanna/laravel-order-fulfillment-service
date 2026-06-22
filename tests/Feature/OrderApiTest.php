<?php

namespace Tests\Feature;

use App\Domain\Enums\OrderStatus;
use App\Domain\Models\Order;
use App\Domain\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_orders(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->getJson('/api/orders');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_order(): void
    {
        $product = Product::factory()->create(['stock' => 50, 'price' => 25.00]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 25.00],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'uuid', 'status', 'total', 'items'],
            ]);

        $this->assertDatabaseHas('orders', ['status' => OrderStatus::Created->value]);
    }

    public function test_can_show_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_show_returns_404_for_missing_order(): void
    {
        $response = $this->getJson('/api/orders/999');

        $response->assertNotFound();
    }

    public function test_can_update_order_status(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Created->value]);

        $response = $this->patchJson("/api/orders/{$order->id}/status", [
            'status' => OrderStatus::Paid->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Paid->value);
    }

    public function test_cannot_transition_to_invalid_status(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Created->value]);

        $response = $this->patchJson("/api/orders/{$order->id}/status", [
            'status' => OrderStatus::Shipped->value,
        ]);

        $response->assertUnprocessable();
    }

    public function test_create_order_reserves_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 10.00],
            ],
        ]);

        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_create_order_fails_with_insufficient_stock(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 10.00],
            ],
        ]);

        $response->assertUnprocessable();
    }

    public function test_idempotency_key_prevents_duplicates(): void
    {
        $product = Product::factory()->create(['stock' => 50]);

        $this->postJson('/api/orders', [
            'idempotency_key' => 'test-key-123',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00],
            ],
        ]);

        $response = $this->postJson('/api/orders', [
            'idempotency_key' => 'test-key-123',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00],
            ],
        ]);

        $response->assertStatus(422);
    }
}
