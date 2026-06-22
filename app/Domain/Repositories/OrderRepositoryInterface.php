<?php

namespace App\Domain\Repositories;

use App\Domain\Models\Order;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;
    public function findByUuid(string $uuid): ?Order;
    public function create(array $data): Order;
    public function save(Order $order): void;
    public function all(): Collection;
}
