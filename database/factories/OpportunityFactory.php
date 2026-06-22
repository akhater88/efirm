<?php

namespace Database\Factories;

use App\Enums\OpportunityStatus;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'contact_id' => Contact::factory()->client(),
            'title' => fake()->sentence(3),
            'status' => OpportunityStatus::Open,
            'estimated_value' => fake()->randomFloat(2, 1000, 100000),
            'currency' => 'USD',
            'expected_close_date' => now()->addDays(30),
        ];
    }

    public function won(): static
    {
        return $this->state(['status' => OpportunityStatus::Won]);
    }

    public function lost(): static
    {
        return $this->state(['status' => OpportunityStatus::Lost]);
    }
}
