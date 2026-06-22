<?php

use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);

Route::apiResource('products', ProductController::class)->only(['index', 'show']);

Route::post('marketplace/import', [MarketplaceController::class, 'import']);
Route::post('marketplace/sync-status', [MarketplaceController::class, 'syncStatus']);