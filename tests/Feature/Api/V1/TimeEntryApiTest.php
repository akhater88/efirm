<?php

use App\Enums\Role;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->user->switchWorkspace($this->workspace);
    Sanctum::actingAs($this->user);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);
});

it('creates a time entry', function () {
    $response = $this->postJson('/api/v1/time-entries', [
        'matter_id' => $this->matter->id,
        'description' => 'Reviewed contract clauses',
        'duration_minutes' => 120,
        'started_at' => now()->subHours(2)->toDateTimeString(),
        'ended_at' => now()->toDateTimeString(),
        'is_billable' => true,
        'billing_rate_per_hour' => 200,
        'currency' => 'USD',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.description', 'Reviewed contract clauses')
        ->assertJsonPath('data.duration_minutes', 120);
});

it('lists time entries', function () {
    TimeEntry::factory()->count(3)->create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'matter_id' => $this->matter->id,
    ]);

    $response = $this->getJson('/api/v1/time-entries');

    $response->assertOk()
        ->assertJsonCount(3, 'data.data');
});

it('calculates billable amount correctly using bcmath', function () {
    $entry = TimeEntry::factory()->create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'matter_id' => $this->matter->id,
        'duration_minutes' => 90,
        'billing_rate_per_hour' => '200.00',
        'is_billable' => true,
    ]);

    // 90 min = 1.5 hours * $200 = $300.00
    expect($entry->billableAmount())->toBe('300.00');
});

it('returns null billable amount for non-billable entries', function () {
    $entry = TimeEntry::factory()->nonBillable()->create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'matter_id' => $this->matter->id,
    ]);

    expect($entry->billableAmount())->toBeNull();
});

it('filters by date range', function () {
    TimeEntry::factory()->create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'matter_id' => $this->matter->id,
        'started_at' => now()->subDays(5),
        'ended_at' => now()->subDays(5)->addHour(),
    ]);

    TimeEntry::factory()->create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'matter_id' => $this->matter->id,
        'started_at' => now()->subDays(30),
        'ended_at' => now()->subDays(30)->addHour(),
    ]);

    $response = $this->getJson('/api/v1/time-entries?from='.now()->subDays(7)->toDateString().'&to='.now()->toDateString());

    $response->assertOk()
        ->assertJsonCount(1, 'data.data');
});

it('returns summary grouped by user', function () {
    TimeEntry::factory()->create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'matter_id' => $this->matter->id,
        'duration_minutes' => 60,
        'started_at' => now()->subDay(),
        'ended_at' => now()->subDay()->addHour(),
    ]);

    TimeEntry::factory()->create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'matter_id' => $this->matter->id,
        'duration_minutes' => 120,
        'started_at' => now(),
        'ended_at' => now()->addHours(2),
    ]);

    $response = $this->getJson('/api/v1/time-entries-summary?from='.now()->subWeek()->toDateString().'&to='.now()->addDay()->toDateString().'&group_by=user');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and((int) $data[0]['total_minutes'])->toBe(180);
});

it('deletes a time entry', function () {
    $entry = TimeEntry::factory()->create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'matter_id' => $this->matter->id,
    ]);

    $response = $this->deleteJson("/api/v1/time-entries/{$entry->id}");

    $response->assertNoContent();
    expect(TimeEntry::find($entry->id))->toBeNull();
});

it('workspace isolation prevents cross-workspace access', function () {
    $otherWorkspace = Workspace::factory()->create();

    TimeEntry::factory()->create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'matter_id' => $this->matter->id,
    ]);

    TimeEntry::factory()->create([
        'workspace_id' => $otherWorkspace->id,
    ]);

    $response = $this->getJson('/api/v1/time-entries');

    $response->assertOk()
        ->assertJsonCount(1, 'data.data');
});
