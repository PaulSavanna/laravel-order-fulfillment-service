<?php

namespace App\Domain\Models;

use App\Domain\Exceptions\InsufficientStock;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    protected $fillable = ['name', 'sku', 'stock', 'price'];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function hasEnoughStock(int $quantity): bool
    {
        return $this->stock >= $quantity;
    }

    public function reserve(int $quantity): void
    {
        if (!$this->hasEnoughStock($quantity)) {
            throw InsufficientStock::forProduct($this, $quantity);
        }
        $this->decrement('stock', $quantity);
    }

    public function release(int $quantity): void
    {
        $this->increment('stock', $quantity);
    }
}
