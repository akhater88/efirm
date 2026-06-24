<?php

namespace App\Enums;

enum SubscriptionEventType: string
{
    case TrialStarted = 'trial_started';
    case Activated = 'activated';
    case PaymentFailed = 'payment_failed';
    case PastDueEntered = 'past_due_entered';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';
    case PlanChanged = 'plan_changed';
    case SeatsChanged = 'seats_changed';
    case Reactivated = 'reactivated';
}
