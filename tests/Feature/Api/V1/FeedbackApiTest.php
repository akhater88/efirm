<?php

use App\Models\Feedback;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function createFeedbackUser(string $role = 'owner'): array
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

it('creates feedback with valid data', function () {
    [$user, $workspace] = createFeedbackUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/feedback', [
        'message' => 'The contract editor is great!',
        'type' => 'general',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.message', 'The contract editor is great!');
    $response->assertJsonPath('data.type', 'general');

    $this->assertDatabaseHas('feedback', [
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
        'message' => 'The contract editor is great!',
    ]);
});

it('creates feedback with bug type', function () {
    [$user, $workspace] = createFeedbackUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/feedback', [
        'message' => 'Export fails on large documents',
        'type' => 'bug',
        'page_url' => '/matters/123/documents/456',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.type', 'bug');
    $response->assertJsonPath('data.page_url', '/matters/123/documents/456');
});

it('lists feedback for owner', function () {
    [$user, $workspace] = createFeedbackUser('owner');

    Feedback::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'message' => 'Test feedback',
        'type' => 'general',
        'created_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/feedback');

    $response->assertOk();
    $response->assertJsonPath('data.0.message', 'Test feedback');
});

it('denies feedback listing to member role', function () {
    [$user, $workspace] = createFeedbackUser('member');

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/feedback');

    $response->assertForbidden();
});

it('validates message is required', function () {
    [$user, $workspace] = createFeedbackUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/feedback', [
        'type' => 'general',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('message');
});

it('validates type must be valid', function () {
    [$user, $workspace] = createFeedbackUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/feedback', [
        'message' => 'Test',
        'type' => 'invalid_type',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('type');
});

it('returns 401 for unauthenticated feedback request', function () {
    $response = $this->postJson('/api/v1/feedback', [
        'message' => 'Test',
    ]);

    $response->assertUnauthorized();
});
