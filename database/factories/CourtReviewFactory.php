<?php

namespace Database\Factories;

use App\Enums\DecisionOutcome;
use App\Enums\DecisionType;
use App\Models\CourtReview;
use App\Models\Matter;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourtReview>
 */
class CourtReviewFactory extends Factory
{
    protected $model = CourtReview::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'matter_id' => Matter::factory(),
            'decision_date' => now(),
            'decision_type' => DecisionType::FinalJudgment,
            'outcome' => DecisionOutcome::Favourable,
            'appealable' => false,
            'appeal_filed' => false,
        ];
    }

    public function appealable(): static
    {
        return $this->state([
            'appealable' => true,
            'appeal_deadline_date' => now()->addDays(30),
        ]);
    }
}
