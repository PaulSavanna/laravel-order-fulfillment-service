<?php

namespace Tests\Feature;

use App\Domain\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_can_show_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('data.sku', $product->sku);
    }

    public function test_show_returns_404_for_missing_product(): void
    {
        $response = $this->getJson('/api/products/999');

        $response->assertNotFound();
    }
}
