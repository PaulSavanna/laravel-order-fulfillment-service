<?php

namespace App\Application\Services;

use App\Domain\Repositories\ProductRepositoryInterface;

class StockService
{
    public function __construct(
        private ProductRepositoryInterface $products,
    ) {}

    public function reserve(array $items): void
    {
        $products = collect($items)->map(fn ($item) => [
            'product' => $this->products->findById($item['product_id']),
            'quantity' => $item['quantity'],
        ]);

        foreach ($products as $entry) {
            $entry['product']->reserve($entry['quantity']);
        }
    }

    public function release(array $items): void
    {
        foreach ($items as $item) {
            $product = $this->products->findById($item['product_id']);
            if ($product) {
                $product->release($item['quantity']);
            }
        }
    }
}
