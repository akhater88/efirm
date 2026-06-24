<?php

use App\Enums\AdminRole;
use App\Enums\SubscriptionEventType;
use App\Enums\SubscriptionState;
use App\Models\AdminUser;
use App\Models\Plan;
use App\Models\SubscriptionEvent;
use App\Models\Workspace;
use App\Services\SubscriptionLifecycleService;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->plan = Plan::create([
        'slug' => 'starter',
        'name' => 'Starter',
        'name_ar' => 'الأساسية',
        'price_per_seat_usd' => 20.00,
        'max_seats' => 3,
    ]);
    $this->admin = AdminUser::create([
        'name' => 'Admin',
        'email' => 'admin@lifecycle.test',
        'password' => bcrypt('password'),
        'role' => AdminRole::SuperAdmin,
    ]);
    $this->service = app(SubscriptionLifecycleService::class);
});

test('can start a trial subscription', function () {
    $subscription = $this->service->startTrial($this->workspace, $this->plan, $this->admin);

    expect($subscription->state)->toBe(SubscriptionState::Trial);
    expect($subscription->workspace_id)->toBe($this->workspace->id);
    expect($subscription->plan_id)->toBe($this->plan->id);
    expect($subscription->trial_ends_at)->not->toBeNull();
    expect($subscription->seat_count)->toBe(1);
});

test('trial start creates subscription event', function () {
    $subscription = $this->service->startTrial($this->workspace, $this->plan);

    $event = SubscriptionEvent::where('subscription_id', $subscription->id)->first();
    expect($event->event_type)->toBe(SubscriptionEventType::TrialStarted);
    expect($event->to_state)->toBe('trial');
});

test('can transition trial to active', function () {
    $subscription = $this->service->startTrial($this->workspace, $this->plan);
    $subscription = $this->service->transition($subscription, SubscriptionState::Active, $this->admin);

    expect($subscription->state)->toBe(SubscriptionState::Active);
});

test('can transition active to past_due', function () {
    $subscription = $this->service->startTrial($this->workspace, $this->plan);
    $subscription = $this->service->transition($subscription, SubscriptionState::Active);
    $subscription = $this->service->transition($subscription, SubscriptionState::PastDue);

    expect($subscription->state)->toBe(SubscriptionState::PastDue);
    expect($subscription->grace_period_ends_at)->not->toBeNull();
});

test('can transition past_due to suspended', function () {
    $subscription = $this->service->startTrial($this->workspace, $this->plan);
    $subscription = $this->service->transition($subscription, SubscriptionState::Active);
    $subscription = $this->service->transition($subscription, SubscriptionState::PastDue);
    $subscription = $this->service->transition($subscription, SubscriptionState::Suspended);

    expect($subscription->state)->toBe(SubscriptionState::Suspended);
});

test('can transition suspended to cancelled', function () {
    $subscription = $this->service->startTrial($this->workspace, $this->plan);
    $subscription = $this->service->transition($subscription, SubscriptionState::Active);
    $subscription = $this->service->transition($subscription, SubscriptionState::PastDue);
    $subscription = $this->service->transition($subscription, SubscriptionState::Suspended);
    $subscription = $this->service->transition($subscription, SubscriptionState::Cancelled);

    expect($subscription->state)->toBe(SubscriptionState::Cancelled);
    expect($subscription->cancelled_at)->not->toBeNull();
});

test('cannot make invalid state transitions', function () {
    $subscription = $this->service->startTrial($this->workspace, $this->plan);

    expect(fn () => $this->service->transition($subscription, SubscriptionState::Suspended))
        ->toThrow(InvalidArgumentException::class);
});

test('cancelled state has no allowed transitions', function () {
    expect(SubscriptionState::Cancelled->allowedTransitions())->toBe([]);
});

test('can change plan on subscription', function () {
    $subscription = $this->service->startTrial($this->workspace, $this->plan);

    $proPlan = Plan::create([
        'slug' => 'pro',
        'name' => 'Pro',
        'name_ar' => 'المتقدمة',
        'price_per_seat_usd' => 25.00,
    ]);

    $subscription = $this->service->changePlan($subscription, $proPlan, $this->admin);

    expect($subscription->plan_id)->toBe($proPlan->id);

    $event = SubscriptionEvent::where('event_type', SubscriptionEventType::PlanChanged->value)->first();
    expect($event)->not->toBeNull();
    expect($event->payload['new_plan_slug'])->toBe('pro');
});

test('can change seat count', function () {
    $subscription = $this->service->startTrial($this->workspace, $this->plan);
    $subscription = $this->service->changeSeats($subscription, 5, $this->admin);

    expect($subscription->seat_count)->toBe(5);

    $event = SubscriptionEvent::where('event_type', SubscriptionEventType::SeatsChanged->value)->first();
    expect($event->payload['old_seat_count'])->toBe(1);
    expect($event->payload['new_seat_count'])->toBe(5);
});

test('subscription events are append-only', function () {
    $subscription = $this->service->startTrial($this->workspace, $this->plan);
    $event = SubscriptionEvent::where('subscription_id', $subscription->id)->first();

    expect(fn () => $event->update(['from_state' => 'modified']))
        ->toThrow(RuntimeException::class);

    expect(fn () => $event->delete())
        ->toThrow(RuntimeException::class);
});

test('each transition creates an event record', function () {
    $subscription = $this->service->startTrial($this->workspace, $this->plan);
    $this->service->transition($subscription, SubscriptionState::Active);

    expect(SubscriptionEvent::where('subscription_id', $subscription->id)->count())->toBe(2);
});

test('subscription state enum labels are localized', function () {
    expect(SubscriptionState::Trial->label())->toBe(__('admin.subscriptions.state_trial'));
    expect(SubscriptionState::Active->label())->toBe(__('admin.subscriptions.state_active'));
});

test('admin lang files have subscription key parity', function () {
    $en = require resource_path('lang/en/admin.php');
    $ar = require resource_path('lang/ar/admin.php');

    expect(array_keys($en['subscriptions']))->toBe(array_keys($ar['subscriptions']));
});
