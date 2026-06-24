<?php

use App\Models\Contact;
use App\Models\Matter;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\SubscriptionEntitlementService;

beforeEach(function () {
    $this->service = new SubscriptionEntitlementService;
    $this->workspace = Workspace::factory()->create();
});

it('denies everything when workspace has no subscription', function () {
    expect($this->service->canAddSeat($this->workspace))->toBeFalse()
        ->and($this->service->canCreateMatter($this->workspace))->toBeFalse()
        ->and($this->service->canCreateContact($this->workspace))->toBeFalse()
        ->and($this->service->hasFeature($this->workspace, 'document_editor'))->toBeFalse()
        ->and($this->service->remainingQuota($this->workspace, 'seats'))->toBe(0);
});

it('denies everything when subscription is cancelled', function () {
    $plan = Plan::factory()->create([
        'max_seats' => 10,
        'features' => ['document_editor'],
    ]);

    Subscription::factory()->cancelled()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $plan->id,
    ]);

    expect($this->service->canAddSeat($this->workspace))->toBeFalse()
        ->and($this->service->canCreateMatter($this->workspace))->toBeFalse()
        ->and($this->service->canCreateContact($this->workspace))->toBeFalse()
        ->and($this->service->hasFeature($this->workspace, 'document_editor'))->toBeFalse()
        ->and($this->service->remainingQuota($this->workspace, 'seats'))->toBe(0);
});

it('allows operations when subscription is active and below caps', function () {
    $plan = Plan::factory()->create([
        'max_seats' => 10,
        'max_matters' => 50,
        'max_contacts' => 100,
        'features' => ['document_editor', 'clause_library'],
    ]);

    Subscription::factory()->active()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $plan->id,
    ]);

    expect($this->service->canAddSeat($this->workspace))->toBeTrue()
        ->and($this->service->canCreateMatter($this->workspace))->toBeTrue()
        ->and($this->service->canCreateContact($this->workspace))->toBeTrue();
});

it('returns false for canAddSeat when at seat limit', function () {
    $plan = Plan::factory()->create([
        'max_seats' => 2,
    ]);

    Subscription::factory()->active()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $plan->id,
    ]);

    WorkspaceMember::factory()->count(2)->create([
        'workspace_id' => $this->workspace->id,
    ]);

    expect($this->service->canAddSeat($this->workspace))->toBeFalse();
});

it('returns true for canAddSeat when below seat limit', function () {
    $plan = Plan::factory()->create([
        'max_seats' => 5,
    ]);

    Subscription::factory()->active()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $plan->id,
    ]);

    WorkspaceMember::factory()->create([
        'workspace_id' => $this->workspace->id,
    ]);

    expect($this->service->canAddSeat($this->workspace))->toBeTrue();
});

it('checks hasFeature correctly', function () {
    $plan = Plan::factory()->create([
        'features' => ['document_editor', 'ai_operations'],
    ]);

    Subscription::factory()->active()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $plan->id,
    ]);

    expect($this->service->hasFeature($this->workspace, 'document_editor'))->toBeTrue()
        ->and($this->service->hasFeature($this->workspace, 'ai_operations'))->toBeTrue()
        ->and($this->service->hasFeature($this->workspace, 'clause_library'))->toBeFalse();
});

it('returns true and null remaining for unlimited caps', function () {
    $plan = Plan::factory()->unlimited()->create([
        'features' => ['document_editor'],
    ]);

    Subscription::factory()->active()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $plan->id,
    ]);

    expect($this->service->canAddSeat($this->workspace))->toBeTrue()
        ->and($this->service->canCreateMatter($this->workspace))->toBeTrue()
        ->and($this->service->canCreateContact($this->workspace))->toBeTrue()
        ->and($this->service->remainingQuota($this->workspace, 'seats'))->toBeNull()
        ->and($this->service->remainingQuota($this->workspace, 'matters'))->toBeNull()
        ->and($this->service->remainingQuota($this->workspace, 'contacts'))->toBeNull();
});

it('calculates remaining quota correctly', function () {
    $plan = Plan::factory()->create([
        'max_seats' => 5,
        'max_matters' => 10,
        'max_contacts' => 20,
    ]);

    Subscription::factory()->active()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $plan->id,
    ]);

    WorkspaceMember::factory()->count(2)->create([
        'workspace_id' => $this->workspace->id,
    ]);

    Matter::factory()->count(3)->create([
        'workspace_id' => $this->workspace->id,
    ]);

    Contact::factory()->count(5)->create([
        'workspace_id' => $this->workspace->id,
    ]);

    expect($this->service->remainingQuota($this->workspace, 'seats'))->toBe(3)
        ->and($this->service->remainingQuota($this->workspace, 'matters'))->toBe(7)
        ->and($this->service->remainingQuota($this->workspace, 'contacts'))->toBe(15);
});

it('allows hasFeature for suspended workspace but denies creates', function () {
    $plan = Plan::factory()->create([
        'max_seats' => 10,
        'features' => ['document_editor', 'clause_library'],
    ]);

    Subscription::factory()->suspended()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $plan->id,
    ]);

    expect($this->service->hasFeature($this->workspace, 'document_editor'))->toBeTrue()
        ->and($this->service->canAddSeat($this->workspace))->toBeFalse()
        ->and($this->service->canCreateMatter($this->workspace))->toBeFalse()
        ->and($this->service->canCreateContact($this->workspace))->toBeFalse();
});

it('allows operations during trial period', function () {
    $plan = Plan::factory()->create([
        'max_seats' => 10,
        'features' => ['document_editor'],
    ]);

    Subscription::factory()->trial()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $plan->id,
    ]);

    expect($this->service->canAddSeat($this->workspace))->toBeTrue()
        ->and($this->service->canCreateMatter($this->workspace))->toBeTrue()
        ->and($this->service->hasFeature($this->workspace, 'document_editor'))->toBeTrue();
});

it('allows operations during past_due grace period', function () {
    $plan = Plan::factory()->create([
        'max_seats' => 10,
        'features' => ['document_editor'],
    ]);

    Subscription::factory()->pastDue()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $plan->id,
    ]);

    expect($this->service->canAddSeat($this->workspace))->toBeTrue()
        ->and($this->service->canCreateMatter($this->workspace))->toBeTrue()
        ->and($this->service->hasFeature($this->workspace, 'document_editor'))->toBeTrue();
});
