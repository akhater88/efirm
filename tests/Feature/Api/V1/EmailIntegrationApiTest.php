<?php

use App\Models\EmailIntegration;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\DB;

function createEmailAuthUser(string $role = 'owner'): array
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

it('creates an email integration', function () {
    [$user, $workspace] = createEmailAuthUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/email-integrations', [
        'provider' => 'gmail',
        'email_address' => 'test@gmail.com',
        'oauth_access_token' => 'access-token-123',
        'oauth_refresh_token' => 'refresh-token-456',
        'oauth_expires_at' => now()->addHour()->toISOString(),
        'scopes_granted' => ['mail.read', 'mail.send'],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.provider', 'gmail');
    $response->assertJsonPath('data.email_address', 'test@gmail.com');
});

it('lists email integrations', function () {
    [$user, $workspace] = createEmailAuthUser();

    EmailIntegration::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'provider' => 'gmail',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/email-integrations');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('shows a single email integration', function () {
    [$user, $workspace] = createEmailAuthUser();

    $integration = EmailIntegration::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/email-integrations/{$integration->id}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $integration->id);
});

it('deletes (disconnects) an email integration', function () {
    [$user, $workspace] = createEmailAuthUser();

    $integration = EmailIntegration::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/email-integrations/{$integration->id}");

    $response->assertNoContent();
    expect(EmailIntegration::find($integration->id))->toBeNull();
    expect(EmailIntegration::withTrashed()->find($integration->id))->not->toBeNull();
});

it('encrypts oauth tokens at rest — raw DB shows ciphertext, not plaintext', function () {
    [$user, $workspace] = createEmailAuthUser();

    $integration = EmailIntegration::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'oauth_access_token' => 'my-secret-access-token',
        'oauth_refresh_token' => 'my-secret-refresh-token',
    ]);

    // Read raw from DB (bypassing Eloquent casts)
    $raw = DB::table('email_integrations')
        ->where('id', $integration->id)
        ->first();

    // Raw value must NOT be the plaintext
    expect($raw->oauth_access_token)->not->toBe('my-secret-access-token');
    expect($raw->oauth_refresh_token)->not->toBe('my-secret-refresh-token');

    // But Eloquent model decrypts correctly
    $fresh = EmailIntegration::withoutGlobalScopes()->find($integration->id);
    expect($fresh->oauth_access_token)->toBe('my-secret-access-token');
    expect($fresh->oauth_refresh_token)->toBe('my-secret-refresh-token');
});

it('never returns oauth tokens in API responses', function () {
    [$user, $workspace] = createEmailAuthUser();

    $integration = EmailIntegration::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'oauth_access_token' => 'secret-access-token',
        'oauth_refresh_token' => 'secret-refresh-token',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/email-integrations/{$integration->id}");

    $response->assertOk();
    $responseData = $response->json('data');
    expect($responseData)->not->toHaveKey('oauth_access_token');
    expect($responseData)->not->toHaveKey('oauth_refresh_token');
});

it('attaches an email to a matter', function () {
    [$user, $workspace] = createEmailAuthUser();

    $integration = EmailIntegration::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);

    $matter = Matter::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/email-attachments', [
        'email_integration_id' => $integration->id,
        'attached_to_type' => 'matter',
        'attached_to_id' => $matter->id,
        'email_provider_id' => 'msg-12345',
        'subject' => 'Contract Discussion',
        'from_address' => 'lawyer@example.com',
        'from_name' => 'Lawyer Name',
        'to_addresses' => ['client@example.com'],
        'received_at' => now()->toISOString(),
        'body_snippet' => 'Please review the attached contract.',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.subject', 'Contract Discussion');
});

it('enforces workspace isolation for email integrations', function () {
    [$user, $workspace] = createEmailAuthUser();
    $otherWorkspace = Workspace::factory()->create();

    $otherIntegration = EmailIntegration::factory()->create([
        'workspace_id' => $otherWorkspace->id,
        'user_id' => User::factory()->create()->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/email-integrations/{$otherIntegration->id}");

    $response->assertNotFound();
});
