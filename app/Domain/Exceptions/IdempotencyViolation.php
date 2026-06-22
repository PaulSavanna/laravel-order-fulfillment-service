<?php

namespace App\Domain\Exceptions;

use InvalidArgumentException;

class IdempotencyViolation extends InvalidArgumentException
{
    public static function forKey(string $key): static
    {
        return new static("Idempotency key [{$key}] has already been used.");
    }
}
