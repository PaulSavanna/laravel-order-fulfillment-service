<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Models\Order;
use App\Domain\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function findById(int $id): ?Order
    {
        return Order::find($id);
    }

    public function findByUuid(string $uuid): ?Order
    {
        return Order::where('uuid', $uuid)->first();
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function save(Order $order): void
    {
        $order->save();
    }

    public function all(): Collection
    {
        return Order::with('items.product')->get();
    }
}
