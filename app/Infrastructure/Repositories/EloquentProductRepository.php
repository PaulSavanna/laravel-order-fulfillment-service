<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Models\Product;
use App\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    public function all(): Collection
    {
        return Product::all();
    }

    public function save(Product $product): void
    {
        $product->save();
    }
}
