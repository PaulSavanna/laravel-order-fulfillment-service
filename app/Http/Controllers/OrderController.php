<?php

namespace App\Http\Controllers;

use App\Application\Services\OrderService;
use App\Domain\Enums\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function index()
    {
        $orders = $this->orderService->listOrders();
        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->createOrder(
            $request->validated(),
            $request->input('idempotency_key'),
        );

        return new OrderResource($order);
    }

    public function show(int $id)
    {
        $order = $this->orderService->getOrder($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return new OrderResource($order);
    }

    public function updateStatus(int $id, UpdateOrderStatusRequest $request)
    {
        $order = $this->orderService->updateStatus(
            $id,
            OrderStatus::from($request->input('status')),
        );

        return new OrderResource($order);
    }
}