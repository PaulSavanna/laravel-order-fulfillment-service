<?php

namespace App\Http\Controllers;

use App\Application\Services\MarketplaceImportService;
use App\Domain\Enums\OrderStatus;
use App\Http\Requests\ImportOrdersRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function __construct(
        private MarketplaceImportService $importService,
    ) {}

    public function import(ImportOrdersRequest $request)
    {
        $imported = $this->importService->importOrders(
            collect($request->input('orders'))
        );

        return OrderResource::collection($imported);
    }

    public function syncStatus(Request $request)
    {
        $request->validate([
            'external_id' => 'required|string',
            'status' => 'required|string|in:' . implode(',', array_column(OrderStatus::cases(), 'value')),
        ]);

        $order = $this->importService->syncStatus(
            $request->input('external_id'),
            OrderStatus::from($request->input('status')),
        );

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return new OrderResource($order);
    }
}