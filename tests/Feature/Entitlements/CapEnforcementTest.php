<?php

use App\Enums\Role;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

beforeEach(function () {
    $this->plan = Plan::factory()->create([
        'max_matters' => 2,
        'max_contacts' => 2,
        'max_seats' => 2,
    ]);
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->actingAs($this->user);
    $this->user->switchWorkspace($this->workspace);

    Subscription::factory()->active()->create([
        'workspace_id' => $this->workspace->id,
        'plan_id' => $this->plan->id,
    ]);
});

test('matter creation is blocked when at plan limit', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);

    // Create matters up to limit
    Matter::factory()->count(2)->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);

    // Try to create one more via API
    $response = $this->postJson('/api/v1/matters', [
        'title' => 'Over Limit Matter',
        'client_id' => $client->id,
        'practice_area' => 'commercial_contracts',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['_subscription']);
});

test('matter creation is allowed when under plan limit', function () {
    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);

    $response = $this->postJson('/api/v1/matters', [
        'title' => 'Under Limit Matter',
        'client_id' => $client->id,
        'practice_area' => 'commercial_contracts',
    ]);

    $response->assertCreated();
});

test('contact creation is blocked when at plan limit', function () {
    // Create contacts up to limit
    Contact::factory()->count(2)->create(['workspace_id' => $this->workspace->id]);

    $response = $this->postJson('/api/v1/contacts', [
        'type' => 'person',
        'first_name' => 'Over',
        'last_name' => 'Limit',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['_subscription']);
});

test('contact creation is allowed when under plan limit', function () {
    $response = $this->postJson('/api/v1/contacts', [
        'type' => 'person',
        'first_name' => 'Under',
        'last_name' => 'Limit',
    ]);

    $response->assertCreated();
});

test('invitation is blocked when at seat limit', function () {
    // Already have 1 member (owner), limit is 2, add one more to hit limit
    $user2 = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $user2->id,
        'workspace_id' => $this->workspace->id,
    ]);

    $response = $this->postJson("/api/v1/workspaces/{$this->workspace->id}/invitations", [
        'email' => 'new@example.com',
        'role' => 'member',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonFragment(['message' => __('admin.entitlements.seat_limit_reached')]);
});

test('unlimited plan allows creation without cap', function () {
    // Switch to unlimited plan
    $unlimitedPlan = Plan::factory()->unlimited()->create();
    Subscription::where('workspace_id', $this->workspace->id)
        ->update(['plan_id' => $unlimitedPlan->id]);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);

    // Create many matters — should all succeed
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/matters', [
            'title' => "Matter {$i}",
            'client_id' => $client->id,
            'practice_area' => 'commercial_contracts',
        ])->assertCreated();
    }
});

test('no subscription allows creation gracefully', function () {
    // Remove subscription — workspaces without subscriptions should still work
    Subscription::where('workspace_id', $this->workspace->id)->delete();

    $response = $this->postJson('/api/v1/contacts', [
        'type' => 'person',
        'first_name' => 'No',
        'last_name' => 'Sub',
    ]);

    // Should pass through — entitlement only enforced when subscription exists
    $response->assertCreated();
});
