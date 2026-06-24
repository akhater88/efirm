<?php

namespace App\Enums;

enum SubscriptionState: string
{
    case Trial = 'trial';
    case Active = 'active';
    case PastDue = 'past_due';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Trial => __('admin.subscriptions.state_trial'),
            self::Active => __('admin.subscriptions.state_active'),
            self::PastDue => __('admin.subscriptions.state_past_due'),
            self::Suspended => __('admin.subscriptions.state_suspended'),
            self::Cancelled => __('admin.subscriptions.state_cancelled'),
        };
    }

    /**
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Trial => [self::Active, self::Cancelled],
            self::Active => [self::PastDue, self::Cancelled],
            self::PastDue => [self::Active, self::Suspended],
            self::Suspended => [self::Active, self::Cancelled],
            self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions());
    }
}
