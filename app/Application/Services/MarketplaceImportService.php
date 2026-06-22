<?php

namespace App\Application\Services;

use App\Domain\Enums\OrderStatus;
use App\Domain\Models\Order;
use App\Domain\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MarketplaceImportService
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private StockService $stock,
    ) {}

    public function importOrders(Collection $externalOrders): array
    {
        $imported = [];

        foreach ($externalOrders as $externalOrder) {
            $order = $this->importSingleOrder($externalOrder);
            $imported[] = $order;
        }

        return $imported;
    }

    private function importSingleOrder(array $externalOrder): Order
    {
        $this->stock->reserve($externalOrder['items']);

        $order = $this->orders->create([
            'uuid' => Str::uuid()->toString(),
            'status' => OrderStatus::Created->value,
            'metadata' => [
                'source' => 'marketplace',
                'external_id' => $externalOrder['external_id'] ?? null,
            ],
        ]);

        foreach ($externalOrder['items'] as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }

        $order->calculateTotal();

        return $order->load('items.product');
    }

    public function syncStatus(string $externalId, OrderStatus $status): ?Order
    {
        $order = $this->orders->all()
            ->first(fn (Order $o) => data_get($o->metadata, 'external_id') === $externalId);

        if (!$order) {
            return null;
        }

        $order->transitionTo($status);

        return $order->fresh('items.product');
    }
}
