<?php

namespace Database\Factories;

use App\Domain\Enums\OrderStatus;
use App\Domain\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'status' => OrderStatus::Created->value,
            'total' => 0,
            'idempotency_key' => null,
            'metadata' => null,
        ];
    }

    public function withIdempotencyKey(string $key): static
    {
        return $this->state(fn () => ['idempotency_key' => $key]);
    }

    public function withStatus(OrderStatus $status): static
    {
        return $this->state(fn () => ['status' => $status->value]);
    }
}
