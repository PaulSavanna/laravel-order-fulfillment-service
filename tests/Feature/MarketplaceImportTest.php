<?php

namespace Tests\Feature;

use App\Domain\Enums\OrderStatus;
use App\Domain\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_marketplace_orders(): void
    {
        $product = Product::factory()->create(['stock' => 50]);

        $response = $this->postJson('/api/marketplace/import', [
            'orders' => [
                [
                    'external_id' => 'EXT-001',
                    'items' => [
                        ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 15.00],
                    ],
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonCount(1, 'data');

        $this->assertDatabaseHas('orders', [
            'status' => OrderStatus::Created->value,
        ]);
    }

    public function test_imported_orders_have_marketplace_metadata(): void
    {
        $product = Product::factory()->create(['stock' => 50]);

        $this->postJson('/api/marketplace/import', [
            'orders' => [
                [
                    'external_id' => 'EXT-002',
                    'items' => [
                        ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 20.00],
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('orders', [
            'metadata' => json_encode(['source' => 'marketplace', 'external_id' => 'EXT-002']),
        ]);
    }

    public function test_can_sync_marketplace_status(): void
    {
        $product = Product::factory()->create(['stock' => 50]);

        $this->postJson('/api/marketplace/import', [
            'orders' => [
                [
                    'external_id' => 'EXT-003',
                    'items' => [
                        ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 20.00],
                    ],
                ],
            ],
        ]);

        $response = $this->postJson('/api/marketplace/sync-status', [
            'external_id' => 'EXT-003',
            'status' => OrderStatus::Paid->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Paid->value);
    }

    public function test_sync_returns_404_for_unknown_external_id(): void
    {
        $response = $this->postJson('/api/marketplace/sync-status', [
            'external_id' => 'UNKNOWN',
            'status' => OrderStatus::Paid->value,
        ]);

        $response->assertNotFound();
    }
}
