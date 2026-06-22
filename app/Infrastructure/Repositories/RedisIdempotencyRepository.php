<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\IdempotencyRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class RedisIdempotencyRepository implements IdempotencyRepositoryInterface
{
    public function isKeyUsed(string $key): bool
    {
        return Cache::has("idempotency:{$key}");
    }

    public function markKeyUsed(string $key, int $ttlSeconds = 3600): void
    {
        Cache::put("idempotency:{$key}", true, $ttlSeconds);
    }
}
