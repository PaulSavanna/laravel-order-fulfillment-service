<?php

namespace Tests\Unit\Domain;

use App\Domain\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_created_can_transition_to_paid(): void
    {
        $this->assertTrue(OrderStatus::Created->canTransitionTo(OrderStatus::Paid));
    }

    public function test_created_can_transition_to_cancelled(): void
    {
        $this->assertTrue(OrderStatus::Created->canTransitionTo(OrderStatus::Cancelled));
    }

    public function test_created_cannot_transition_to_shipped(): void
    {
        $this->assertFalse(OrderStatus::Created->canTransitionTo(OrderStatus::Shipped));
    }

    public function test_paid_can_transition_to_packed(): void
    {
        $this->assertTrue(OrderStatus::Paid->canTransitionTo(OrderStatus::Packed));
    }

    public function test_paid_can_transition_to_cancelled(): void
    {
        $this->assertTrue(OrderStatus::Paid->canTransitionTo(OrderStatus::Cancelled));
    }

    public function test_packed_can_transition_to_shipped(): void
    {
        $this->assertTrue(OrderStatus::Packed->canTransitionTo(OrderStatus::Shipped));
    }

    public function test_shipped_can_transition_to_delivered(): void
    {
        $this->assertTrue(OrderStatus::Shipped->canTransitionTo(OrderStatus::Delivered));
    }

    public function test_delivered_cannot_transition_anywhere(): void
    {
        $this->assertEmpty(OrderStatus::Delivered->allowedTransitions());
    }

    public function test_cancelled_cannot_transition_anywhere(): void
    {
        $this->assertEmpty(OrderStatus::Cancelled->allowedTransitions());
    }

    public function test_invalid_transition_returns_false(): void
    {
        $this->assertFalse(OrderStatus::Created->canTransitionTo(OrderStatus::Delivered));
    }
}
