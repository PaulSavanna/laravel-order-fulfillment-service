<?php

namespace App\Domain\Models;

use App\Domain\Enums\OrderStatus;
use App\Domain\Exceptions\InvalidStatusTransition;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    protected $fillable = ['uuid', 'status', 'total', 'idempotency_key', 'metadata'];

    protected $casts = [
        'total' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatus(): OrderStatus
    {
        return OrderStatus::from($this->status);
    }

    public function transitionTo(OrderStatus $newStatus): void
    {
        $current = $this->getStatus();

        if (!$current->canTransitionTo($newStatus)) {
            throw InvalidStatusTransition::between($current, $newStatus);
        }

        $this->update(['status' => $newStatus->value]);
    }

    public function calculateTotal(): float
    {
        $total = $this->items->sum(fn (OrderItem $item) => $item->subtotal());
        $this->update(['total' => $total]);
        return $total;
    }
}
