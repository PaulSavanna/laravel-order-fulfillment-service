<?php

namespace App\Domain\Repositories;

use App\Domain\Models\Product;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;
    public function findBySku(string $sku): ?Product;
    public function all(): Collection;
    public function save(Product $product): void;
}
