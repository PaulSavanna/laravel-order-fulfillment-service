<?php

namespace Tests\Unit\Domain;

use App\Domain\Exceptions\InsufficientStock;
use App\Domain\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_reserves_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $product->reserve(3);

        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_product_releases_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $product->release(5);

        $this->assertEquals(15, $product->fresh()->stock);
    }

    public function test_product_throws_on_insufficient_stock(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $this->expectException(InsufficientStock::class);
        $product->reserve(5);
    }

    public function test_product_has_enough_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->assertTrue($product->hasEnoughStock(10));
        $this->assertFalse($product->hasEnoughStock(11));
    }
}
