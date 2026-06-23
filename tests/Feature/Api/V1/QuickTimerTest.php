<?php

/**
 * F-FIX-02.4 — Contextual Quick Timer tests.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 3.5, Decision #31.
 */

use App\Models\Contact;
use App\Models\Court;
use App\Models\Hearing;
use App\Models\Matter;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupTimerUser(string $role = 'owner'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);
    $client = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $matter = Matter::factory()->litigation()->create(['workspace_id' => $workspace->id, 'client_id' => $client->id]);
    $court = Court::factory()->create(['workspace_id' => $workspace->id]);

    return [$user, $workspace, $matter, $court];
}

// 1. start from matter creates active time entry
it('start from matter creates active time entry', function () {
    [$user, $workspace, $matter] = setupTimerUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/time-entries/start', [
        'matter_id' => $matter->id,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.matter_id', $matter->id);
    $response->assertJsonPath('data.started_via_context', 'matter');

    $entry = TimeEntry::first();
    expect($entry)->not->toBeNull();
    expect($entry->ended_at)->toBeNull();
    expect($entry->user_id)->toBe($user->id);
    expect($entry->is_billable)->toBeTrue();
});

// 2. start from hearing creates active time entry linked to hearing's matter
it('start from hearing creates active time entry linked to hearing matter', function () {
    [$user, $workspace, $matter, $court] = setupTimerUser();

    $hearing = Hearing::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'court_id' => $court->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/time-entries/start', [
        'hearing_id' => $hearing->id,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.matter_id', $matter->id);
    $response->assertJsonPath('data.started_via_context', 'hearing');
});

// 3. cannot start second timer when one is active (409)
it('cannot start second timer when one is active', function () {
    [$user, $workspace, $matter] = setupTimerUser();

    // Start first timer
    $this->actingAs($user, 'sanctum')->postJson('/api/v1/time-entries/start', [
        'matter_id' => $matter->id,
    ])->assertCreated();

    // Try starting second
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/time-entries/start', [
        'matter_id' => $matter->id,
    ]);

    $response->assertStatus(409);
});

// 4. stop sets ended_at and calculates duration
it('stop sets ended_at and calculates duration', function () {
    [$user, $workspace, $matter] = setupTimerUser();

    // Start timer
    $startResponse = $this->actingAs($user, 'sanctum')->postJson('/api/v1/time-entries/start', [
        'matter_id' => $matter->id,
    ]);

    $entryId = $startResponse->json('data.id');

    // Manually backdate started_at to ensure meaningful duration
    TimeEntry::withoutGlobalScopes()->where('id', $entryId)->update([
        'started_at' => now()->subMinutes(45),
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/time-entries/{$entryId}/stop", [
        'description' => 'Research on contract terms',
    ]);

    $response->assertOk();
    $data = $response->json('data');
    expect($data['ended_at'])->not->toBeNull();
    expect($data['duration_minutes'])->toBeGreaterThanOrEqual(44);
    expect($data['description'])->toBe('Research on contract terms');
});

// 5. stop with adjusted_duration uses adjustment
it('stop with adjusted_duration uses adjustment', function () {
    [$user, $workspace, $matter] = setupTimerUser();

    $startResponse = $this->actingAs($user, 'sanctum')->postJson('/api/v1/time-entries/start', [
        'matter_id' => $matter->id,
    ]);

    $entryId = $startResponse->json('data.id');

    $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/time-entries/{$entryId}/stop", [
        'adjusted_duration_minutes' => 30,
    ]);

    $response->assertOk();
    expect($response->json('data.duration_minutes'))->toBe(30);
});

// 6. active endpoint returns current timer
it('active endpoint returns current timer', function () {
    [$user, $workspace, $matter] = setupTimerUser();

    $this->actingAs($user, 'sanctum')->postJson('/api/v1/time-entries/start', [
        'matter_id' => $matter->id,
    ])->assertCreated();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/time-entries/active');

    $response->assertOk();
    $response->assertJsonPath('data.matter_id', $matter->id);
});

// 7. active endpoint returns 204 when no active timer
it('active endpoint returns 204 when no active timer', function () {
    [$user] = setupTimerUser();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/time-entries/active');

    $response->assertNoContent();
});

// 8. workspace isolation — active timer check is per-user globally
it('workspace isolation - active timer check is per-user globally', function () {
    [$user, $workspace, $matter] = setupTimerUser();

    // Create a second workspace
    $workspace2 = Workspace::factory()->create();
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace2->id,
    ]);
    $client2 = Contact::factory()->client()->create(['workspace_id' => $workspace2->id]);
    $matter2 = Matter::factory()->litigation()->create(['workspace_id' => $workspace2->id, 'client_id' => $client2->id]);

    // Start timer in workspace 1
    $this->actingAs($user, 'sanctum')->postJson('/api/v1/time-entries/start', [
        'matter_id' => $matter->id,
    ])->assertCreated();

    // Switch to workspace 2
    $user->switchWorkspace($workspace2);

    // Cannot start timer in workspace 2 either (global per-user constraint)
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/time-entries/start', [
        'matter_id' => $matter2->id,
    ]);

    $response->assertStatus(409);
});
