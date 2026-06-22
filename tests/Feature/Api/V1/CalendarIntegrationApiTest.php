<?php

use App\Models\CalendarIntegration;
use App\Models\ExternalCalendarEvent;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\DB;

function createCalendarAuthUser(string $role = 'owner'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);

    return [$user, $workspace];
}

it('creates a calendar integration', function () {
    [$user, $workspace] = createCalendarAuthUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/calendar-integrations', [
        'provider' => 'google',
        'calendar_id' => 'primary',
        'oauth_access_token' => 'cal-access-token',
        'oauth_refresh_token' => 'cal-refresh-token',
        'oauth_expires_at' => now()->addHour()->toISOString(),
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.provider', 'google');
});

it('lists calendar integrations', function () {
    [$user, $workspace] = createCalendarAuthUser();

    CalendarIntegration::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'provider' => 'google',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/calendar-integrations');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('upserts calendar events without duplicates', function () {
    [$user, $workspace] = createCalendarAuthUser();

    $integration = CalendarIntegration::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);

    $eventData = [
        'events' => [
            [
                'provider_event_id' => 'evt-001',
                'title' => 'Client Meeting',
                'starts_at' => now()->addDay()->toISOString(),
                'ends_at' => now()->addDay()->addHour()->toISOString(),
                'timezone' => 'Asia/Amman',
            ],
        ],
    ];

    // First upsert
    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/calendar-integrations/{$integration->id}/events", $eventData)
        ->assertOk();

    // Second upsert with same provider_event_id — should not create duplicate
    $eventData['events'][0]['title'] = 'Updated Client Meeting';
    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/calendar-integrations/{$integration->id}/events", $eventData)
        ->assertOk();

    $events = ExternalCalendarEvent::withoutGlobalScopes()
        ->where('calendar_integration_id', $integration->id)
        ->get();

    expect($events)->toHaveCount(1);
    expect($events->first()->title)->toBe('Updated Client Meeting');
});

it('encrypts calendar oauth tokens at rest', function () {
    [$user, $workspace] = createCalendarAuthUser();

    $integration = CalendarIntegration::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'oauth_access_token' => 'cal-secret-access',
        'oauth_refresh_token' => 'cal-secret-refresh',
    ]);

    $raw = DB::table('calendar_integrations')
        ->where('id', $integration->id)
        ->first();

    expect($raw->oauth_access_token)->not->toBe('cal-secret-access');
    expect($raw->oauth_refresh_token)->not->toBe('cal-secret-refresh');

    $fresh = CalendarIntegration::withoutGlobalScopes()->find($integration->id);
    expect($fresh->oauth_access_token)->toBe('cal-secret-access');
    expect($fresh->oauth_refresh_token)->toBe('cal-secret-refresh');
});

it('never returns calendar oauth tokens in API responses', function () {
    [$user, $workspace] = createCalendarAuthUser();

    $integration = CalendarIntegration::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/calendar-integrations/{$integration->id}");

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->not->toHaveKey('oauth_access_token');
    expect($data)->not->toHaveKey('oauth_refresh_token');
});

it('enforces workspace isolation for calendar integrations', function () {
    [$user, $workspace] = createCalendarAuthUser();
    $otherWorkspace = Workspace::factory()->create();

    $otherIntegration = CalendarIntegration::factory()->create([
        'workspace_id' => $otherWorkspace->id,
        'user_id' => User::factory()->create()->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/calendar-integrations/{$otherIntegration->id}");

    $response->assertNotFound();
});
