<?php

namespace App\Providers;

use App\Application\Services\MarketplaceImportService;
use App\Application\Services\OrderService;
use App\Application\Services\StockService;
use App\Domain\Repositories\IdempotencyRepositoryInterface;
use App\Domain\Repositories\OrderRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Cache\StockCache;
use App\Infrastructure\Repositories\EloquentOrderRepository;
use App\Infrastructure\Repositories\EloquentProductRepository;
use App\Infrastructure\Repositories\RedisIdempotencyRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(IdempotencyRepositoryInterface::class, RedisIdempotencyRepository::class);
        $this->app->singleton(StockCache::class);
        $this->app->singleton(StockService::class);
        $this->app->singleton(OrderService::class);
        $this->app->singleton(MarketplaceImportService::class);
    }

    public function boot(): void
    {
        //
    }
}
