<?php

use App\Enums\Role;
use App\Mail\WorkspaceInvitationMail;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Mail;

function setupInvitationUser(string $role = 'owner'): array
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

it('creates an invitation and sends email', function () {
    Mail::fake();
    [$user, $workspace] = setupInvitationUser();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/workspaces/{$workspace->id}/invitations", [
            'email' => 'invitee@example.com',
            'role' => 'member',
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('workspace_invitations', [
        'email' => 'invitee@example.com',
        'workspace_id' => $workspace->id,
    ]);
    Mail::assertSent(WorkspaceInvitationMail::class);
});

it('lists pending invitations for workspace', function () {
    [$user, $workspace] = setupInvitationUser();
    WorkspaceInvitation::factory()->create(['workspace_id' => $workspace->id, 'invited_by_user_id' => $user->id]);
    WorkspaceInvitation::factory()->expired()->create(['workspace_id' => $workspace->id, 'invited_by_user_id' => $user->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/workspaces/{$workspace->id}/invitations");

    $response->assertOk();
    // Only pending (non-expired, non-accepted) should be returned
    $response->assertJsonCount(1, 'data');
});

it('revokes an invitation', function () {
    [$user, $workspace] = setupInvitationUser();
    $invitation = WorkspaceInvitation::factory()->create([
        'workspace_id' => $workspace->id,
        'invited_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/workspaces/{$workspace->id}/invitations/{$invitation->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('workspace_invitations', ['id' => $invitation->id]);
});

it('denies invitation creation to Member role', function () {
    Mail::fake();
    [$user, $workspace] = setupInvitationUser('member');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/workspaces/{$workspace->id}/invitations", [
            'email' => 'invitee@example.com',
            'role' => 'member',
        ]);

    $response->assertForbidden();
});

it('accepts invitation via API', function () {
    [$owner, $workspace] = setupInvitationUser();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);
    $invitation = WorkspaceInvitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'invitee@example.com',
        'role' => Role::Member,
        'invited_by_user_id' => $owner->id,
    ]);

    $response = $this->actingAs($invitee, 'sanctum')
        ->postJson('/api/v1/invitations/accept', [
            'token' => $invitation->token,
        ]);

    $response->assertOk();
    expect($invitee->belongsToWorkspace($workspace))->toBeTrue();
});

it('rejects expired invitation', function () {
    [$owner, $workspace] = setupInvitationUser();
    $invitee = User::factory()->create(['email' => 'expired@example.com']);
    $invitation = WorkspaceInvitation::factory()->expired()->create([
        'workspace_id' => $workspace->id,
        'email' => 'expired@example.com',
        'invited_by_user_id' => $owner->id,
    ]);

    $response = $this->actingAs($invitee, 'sanctum')
        ->postJson('/api/v1/invitations/accept', [
            'token' => $invitation->token,
        ]);

    $response->assertUnprocessable();
});

it('rejects already accepted invitation', function () {
    [$owner, $workspace] = setupInvitationUser();
    $invitee = User::factory()->create(['email' => 'accepted@example.com']);
    $invitation = WorkspaceInvitation::factory()->accepted()->create([
        'workspace_id' => $workspace->id,
        'email' => 'accepted@example.com',
        'invited_by_user_id' => $owner->id,
    ]);

    $response = $this->actingAs($invitee, 'sanctum')
        ->postJson('/api/v1/invitations/accept', [
            'token' => $invitation->token,
        ]);

    $response->assertUnprocessable();
});

it('rejects invitation when email does not match', function () {
    [$owner, $workspace] = setupInvitationUser();
    $wrongUser = User::factory()->create(['email' => 'wrong@example.com']);
    $invitation = WorkspaceInvitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'correct@example.com',
        'invited_by_user_id' => $owner->id,
    ]);

    $response = $this->actingAs($wrongUser, 'sanctum')
        ->postJson('/api/v1/invitations/accept', [
            'token' => $invitation->token,
        ]);

    $response->assertUnprocessable();
});

it('enforces rate limit of 5 invitations per email per day', function () {
    Mail::fake();
    [$user, $workspace] = setupInvitationUser();

    // Create 5 recent invitations
    WorkspaceInvitation::factory()->count(5)->create([
        'workspace_id' => $workspace->id,
        'email' => 'spammed@example.com',
        'invited_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/workspaces/{$workspace->id}/invitations", [
            'email' => 'spammed@example.com',
            'role' => 'member',
        ]);

    $response->assertUnprocessable();
});

it('rejects inviting existing workspace member', function () {
    Mail::fake();
    [$user, $workspace] = setupInvitationUser();
    $existingMember = User::factory()->create(['email' => 'member@example.com']);
    WorkspaceMember::factory()->create([
        'user_id' => $existingMember->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/workspaces/{$workspace->id}/invitations", [
            'email' => 'member@example.com',
            'role' => 'member',
        ]);

    $response->assertUnprocessable();
});

it('returns 401 for unauthenticated invitation request', function () {
    $workspace = Workspace::factory()->create();

    $response = $this->getJson("/api/v1/workspaces/{$workspace->id}/invitations");

    $response->assertUnauthorized();
});
