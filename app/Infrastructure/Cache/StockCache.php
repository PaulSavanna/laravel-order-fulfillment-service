<?php

namespace App\Infrastructure\Cache;

use Illuminate\Support\Facades\Cache;

class StockCache
{
    public function getStock(int $productId): ?int
    {
        return Cache::get("stock:{$productId}");
    }

    public function setStock(int $productId, int $stock): void
    {
        Cache::set("stock:{$productId}", $stock, 300);
    }

    public function invalidate(int $productId): void
    {
        Cache::forget("stock:{$productId}");
    }
}
