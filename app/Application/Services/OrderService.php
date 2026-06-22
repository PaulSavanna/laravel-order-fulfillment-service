<?php

namespace App\Application\Services;

use App\Domain\Enums\OrderStatus;
use App\Domain\Exceptions\IdempotencyViolation;
use App\Domain\Models\Order;
use App\Domain\Repositories\IdempotencyRepositoryInterface;
use App\Domain\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private IdempotencyRepositoryInterface $idempotency,
        private StockService $stock,
    ) {}

    public function createOrder(array $data, ?string $idempotencyKey = null): Order
    {
        if ($idempotencyKey && $this->idempotency->isKeyUsed($idempotencyKey)) {
            throw IdempotencyViolation::forKey($idempotencyKey);
        }

        $this->stock->reserve($data['items']);

        $order = $this->orders->create([
            'uuid' => Str::uuid()->toString(),
            'status' => OrderStatus::Created->value,
            'idempotency_key' => $idempotencyKey,
        ]);

        foreach ($data['items'] as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }

        $order->calculateTotal();

        if ($idempotencyKey) {
            $this->idempotency->markKeyUsed($idempotencyKey);
        }

        return $order->load('items.product');
    }

    public function getOrder(int $id): ?Order
    {
        return $this->orders->findById($id);
    }

    public function getOrderByUuid(string $uuid): ?Order
    {
        return $this->orders->findByUuid($uuid);
    }

    public function updateStatus(int $orderId, OrderStatus $newStatus): Order
    {
        $order = $this->orders->findById($orderId);
        $order->transitionTo($newStatus);

        if ($newStatus === OrderStatus::Cancelled) {
            $this->stock->release(
                $order->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                ])->toArray()
            );
        }

        return $order->fresh('items.product');
    }

    public function listOrders(): \Illuminate\Support\Collection
    {
        return $this->orders->all();
    }
}
