<?php

namespace Database\Factories;

use App\Domain\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-??')),
            'stock' => fake()->numberBetween(10, 100),
            'price' => fake()->randomFloat(2, 5, 500),
        ];
    }
}
