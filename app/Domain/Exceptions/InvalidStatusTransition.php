<?php

namespace App\Domain\Exceptions;

use App\Domain\Enums\OrderStatus;
use InvalidArgumentException;

class InvalidStatusTransition extends InvalidArgumentException
{
    public static function between(OrderStatus $from, OrderStatus $to): static
    {
        return new static("Cannot transition order from [{$from->value}] to [{$to->value}].");
    }
}
