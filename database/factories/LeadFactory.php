<?php

namespace Database\Factories;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'title' => fake()->sentence(3),
            'source' => LeadSource::Referral,
            'status' => LeadStatus::New,
        ];
    }

    public function qualified(): static
    {
        return $this->state(['status' => LeadStatus::Qualified]);
    }

    public function converted(): static
    {
        return $this->state(['status' => LeadStatus::Converted]);
    }
}
