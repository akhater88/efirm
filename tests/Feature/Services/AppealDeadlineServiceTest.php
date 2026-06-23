<?php

/**
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 2, Decision #18.
 *
 * Tests for AppealDeadlineService — court-level appeal window logic per
 * advisor input from Khaldoun Khater (Al-Dujani Office, Amman), 2026-06-23.
 */

use App\Enums\CourtLevel;
use App\Enums\JudgmentPresence;
use App\Enums\Role;
use App\Exceptions\MissingJudgmentPresenceException;
use App\Exceptions\UnconfirmedRegulationException;
use App\Exceptions\UnsupportedCourtLevelException;
use App\Models\Contact;
use App\Models\CourtReview;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\AppealDeadlineService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->user->switchWorkspace($this->workspace);
    auth()->login($this->user);

    $this->client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->service = app(AppealDeadlineService::class);
});

it('calculates magistrate + wijahi as 10 days from day after decision_date', function () {
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
        'court_level' => CourtLevel::Magistrate,
    ]);

    $review = CourtReview::factory()->appealable()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'decision_date' => Carbon::parse('2026-06-01'),
        'judgment_presence' => JudgmentPresence::Wijahi,
    ]);

    $deadline = $this->service->calculate($review);

    expect($deadline->toDateString())->toBe('2026-06-12');
});

it('calculates magistrate + mithla_wijahi as 10 days from day after notified_date', function () {
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
        'court_level' => CourtLevel::Magistrate,
    ]);

    $review = CourtReview::factory()->appealable()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'decision_date' => Carbon::parse('2026-06-01'),
        'judgment_presence' => JudgmentPresence::MithlaWijahi,
        'notified_date' => Carbon::parse('2026-06-05'),
    ]);

    $deadline = $this->service->calculate($review);

    expect($deadline->toDateString())->toBe('2026-06-16');
});

it('calculates first_instance + wijahi as 30 days from day after decision_date', function () {
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
        'court_level' => CourtLevel::FirstInstance,
    ]);

    $review = CourtReview::factory()->appealable()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'decision_date' => Carbon::parse('2026-06-01'),
        'judgment_presence' => JudgmentPresence::Wijahi,
    ]);

    $deadline = $this->service->calculate($review);

    expect($deadline->toDateString())->toBe('2026-07-02');
});

it('calculates first_instance + mithla_wijahi as 30 days from day after notified_date', function () {
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
        'court_level' => CourtLevel::FirstInstance,
    ]);

    $review = CourtReview::factory()->appealable()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'decision_date' => Carbon::parse('2026-06-01'),
        'judgment_presence' => JudgmentPresence::MithlaWijahi,
        'notified_date' => Carbon::parse('2026-06-05'),
    ]);

    $deadline = $this->service->calculate($review);

    expect($deadline->toDateString())->toBe('2026-07-06');
});

it('throws UnconfirmedRegulationException for ghyabi judgment presence', function () {
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
        'court_level' => CourtLevel::Magistrate,
    ]);

    $review = CourtReview::factory()->appealable()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'decision_date' => Carbon::parse('2026-06-01'),
        'judgment_presence' => JudgmentPresence::Ghyabi,
    ]);

    $this->service->calculate($review);
})->throws(UnconfirmedRegulationException::class);

it('throws UnsupportedCourtLevelException for appeal court level', function () {
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
        'court_level' => CourtLevel::Appeal,
    ]);

    $review = CourtReview::factory()->appealable()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'decision_date' => Carbon::parse('2026-06-01'),
        'judgment_presence' => JudgmentPresence::Wijahi,
    ]);

    $this->service->calculate($review);
})->throws(UnsupportedCourtLevelException::class);

it('throws UnsupportedCourtLevelException for null court_level', function () {
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
        'court_level' => null,
    ]);

    $review = CourtReview::factory()->appealable()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'decision_date' => Carbon::parse('2026-06-01'),
        'judgment_presence' => JudgmentPresence::Wijahi,
    ]);

    $this->service->calculate($review);
})->throws(UnsupportedCourtLevelException::class);

it('throws MissingJudgmentPresenceException for null judgment_presence', function () {
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
        'court_level' => CourtLevel::Magistrate,
    ]);

    $review = CourtReview::factory()->appealable()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'decision_date' => Carbon::parse('2026-06-01'),
        'judgment_presence' => null,
    ]);

    $this->service->calculate($review);
})->throws(MissingJudgmentPresenceException::class);

it('throws MissingJudgmentPresenceException for mithla_wijahi with null notified_date', function () {
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
        'court_level' => CourtLevel::Magistrate,
    ]);

    $review = CourtReview::factory()->appealable()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'decision_date' => Carbon::parse('2026-06-01'),
        'judgment_presence' => JudgmentPresence::MithlaWijahi,
        'notified_date' => null,
    ]);

    $this->service->calculate($review);
})->throws(MissingJudgmentPresenceException::class);

it('leaves existing court_reviews without new fields valid (backward compat)', function () {
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
    ]);

    $review = CourtReview::factory()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'decision_date' => Carbon::parse('2026-06-01'),
    ]);

    // Existing reviews without judgment_presence and notified_date should still persist fine
    $review->refresh();

    expect($review->judgment_presence)->toBeNull()
        ->and($review->notified_date)->toBeNull()
        ->and($review->decision_date->toDateString())->toBe('2026-06-01');
});

it('throws UnsupportedCourtLevelException for cassation court level', function () {
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
        'court_level' => CourtLevel::Cassation,
    ]);

    $review = CourtReview::factory()->appealable()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'decision_date' => Carbon::parse('2026-06-01'),
        'judgment_presence' => JudgmentPresence::Wijahi,
    ]);

    $this->service->calculate($review);
})->throws(UnsupportedCourtLevelException::class);

it('throws UnsupportedCourtLevelException for specialized_commercial court level', function () {
    $matter = Matter::factory()->litigation()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $this->client->id,
        'court_level' => CourtLevel::SpecializedCommercial,
    ]);

    $review = CourtReview::factory()->appealable()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $matter->id,
        'decision_date' => Carbon::parse('2026-06-01'),
        'judgment_presence' => JudgmentPresence::Wijahi,
    ]);

    $this->service->calculate($review);
})->throws(UnsupportedCourtLevelException::class);
