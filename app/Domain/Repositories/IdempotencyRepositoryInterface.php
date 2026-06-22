<?php

namespace App\Domain\Repositories;

interface IdempotencyRepositoryInterface
{
    public function isKeyUsed(string $key): bool;
    public function markKeyUsed(string $key, int $ttlSeconds = 3600): void;
}
