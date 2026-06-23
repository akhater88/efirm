<?php

namespace Database\Factories;

use App\Enums\ExpertReportPosition;
use App\Enums\ExpertReportType;
use App\Models\ExpertReport;
use App\Models\Matter;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for ExpertReport model.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #3 and #19.
 *
 * @extends Factory<ExpertReport>
 */
class ExpertReportFactory extends Factory
{
    protected $model = ExpertReport::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'matter_id' => Matter::factory()->litigation(),
            'expert_name_ar' => 'خبير '.fake()->firstName(),
            'expert_name_en' => 'Expert '.fake()->lastName(),
            'report_type' => ExpertReportType::DamagesCalculation,
            'received_date' => now(),
            'our_position' => ExpertReportPosition::NotYetReviewed,
        ];
    }

    public function objectionFiled(): static
    {
        return $this->state([
            'objection_filed' => true,
            'objection_filed_date' => now(),
            'our_position' => ExpertReportPosition::Objected,
        ]);
    }
}
