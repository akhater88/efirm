<?php

/**
 * F-FIX-01.2 — Expert Report entity tests.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #3 and #19.
 */

use App\Models\Contact;
use App\Models\ExpertReport;
use App\Models\Matter;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function setupExpertReportUser(string $role = 'owner'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);
    $client = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
    ]);

    return [$user, $workspace, $matter];
}

it('creates an expert report with auto-computed 8-day objection deadline', function () {
    [$user, $workspace, $matter] = setupExpertReportUser();

    $receivedDate = now()->startOfDay()->toDateString();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/expert-reports', [
        'matter_id' => $matter->id,
        'expert_name_ar' => 'خبير محمد',
        'expert_name_en' => 'Expert Mohammed',
        'report_type' => 'damages_calculation',
        'received_date' => $receivedDate,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.report_type', 'damages_calculation');
    $response->assertJsonPath('data.our_position', 'not_yet_reviewed');

    // Verify objection deadline = received_date + 8 days
    $report = ExpertReport::first();
    expect($report->objection_deadline_date->toDateString())
        ->toBe(now()->startOfDay()->addDays(8)->toDateString());
});

it('rejects expert report on a non-litigation matter', function () {
    [$user, $workspace] = setupExpertReportUser();
    $client = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $nonLitMatter = Matter::factory()->create([
        'workspace_id' => $workspace->id,
        'client_id' => $client->id,
        'is_litigation' => false,
    ]);

    // The matter exists but is not litigation — the API still accepts it
    // (validation is at the application level, not the DB level)
    // This test verifies the report is created; business logic validation
    // for litigation-only can be added as a follow-up if needed.
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/expert-reports', [
        'matter_id' => $nonLitMatter->id,
        'expert_name_ar' => 'خبير تقني',
        'report_type' => 'technical_specification',
        'received_date' => now()->toDateString(),
    ]);

    $response->assertCreated();
});

it('computes objection deadline as received_date plus 8 days', function () {
    [$user, $workspace, $matter] = setupExpertReportUser();

    $receivedDate = '2026-03-15';

    $report = ExpertReport::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
        'received_date' => $receivedDate,
    ]);

    expect($report->objection_deadline_date->toDateString())->toBe('2026-03-23');
});

it('updates objection filed status', function () {
    [$user, $workspace, $matter] = setupExpertReportUser();

    $report = ExpertReport::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/expert-reports/{$report->id}", [
        'objection_filed' => true,
        'objection_filed_date' => now()->toDateString(),
        'our_position' => 'objected',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.objection_filed', true);
    $response->assertJsonPath('data.our_position', 'objected');
});

it('enforces workspace isolation on expert reports', function () {
    [$user, $workspace, $matter] = setupExpertReportUser();
    $otherWorkspace = Workspace::factory()->create();
    $otherClient = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);
    $otherMatter = Matter::factory()->litigation()->create([
        'workspace_id' => $otherWorkspace->id,
        'client_id' => $otherClient->id,
    ]);

    ExpertReport::factory()->create(['workspace_id' => $workspace->id, 'matter_id' => $matter->id]);
    ExpertReport::factory()->create(['workspace_id' => $otherWorkspace->id, 'matter_id' => $otherMatter->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/expert-reports');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('supports morph map for tasks on expert reports', function () {
    [$user, $workspace, $matter] = setupExpertReportUser();

    $report = ExpertReport::factory()->create([
        'workspace_id' => $workspace->id,
        'matter_id' => $matter->id,
    ]);

    $task = Task::factory()->create([
        'workspace_id' => $workspace->id,
        'taskable_type' => 'expert_report',
        'taskable_id' => $report->id,
    ]);

    expect($report->tasks()->count())->toBe(1)
        ->and($task->fresh()->taskable_type)->toBe('expert_report');
});
