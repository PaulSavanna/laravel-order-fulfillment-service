<?php

namespace Tests\Unit\Domain;

use App\Domain\Enums\OrderStatus;
use App\Domain\Exceptions\InvalidStatusTransition;
use App\Domain\Models\Order;
use App\Domain\Models\OrderItem;
use App\Domain\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_get_status(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Paid->value]);

        $this->assertEquals(OrderStatus::Paid, $order->getStatus());
    }

    public function test_order_transition_to_valid_status(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Created->value]);

        $order->transitionTo(OrderStatus::Paid);

        $this->assertEquals(OrderStatus::Paid->value, $order->fresh()->status);
    }

    public function test_order_transition_to_invalid_status_throws(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Created->value]);

        $this->expectException(InvalidStatusTransition::class);
        $order->transitionTo(OrderStatus::Shipped);
    }

    public function test_order_calculate_total(): void
    {
        $product = Product::factory()->create(['price' => 10.00]);
        $order = Order::factory()->create();

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 10.00,
        ]);

        $total = $order->calculateTotal();

        $this->assertEquals(30.00, $total);
        $this->assertEquals(30.00, $order->fresh()->total);
    }
}
