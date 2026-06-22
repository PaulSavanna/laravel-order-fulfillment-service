<?php

namespace App\Domain\Exceptions;

use App\Domain\Models\Product;
use InvalidArgumentException;

class InsufficientStock extends InvalidArgumentException
{
    public static function forProduct(Product $product, int $requested): static
    {
        return new static("Insufficient stock for product [{$product->sku}]. Requested: {$requested}, available: {$product->stock}.");
    }
}
