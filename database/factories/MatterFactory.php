<?php

namespace Database\Factories;

use App\Enums\LitigationStatus;
use App\Enums\MatterStatus;
use App\Enums\PracticeArea;
use App\Enums\RepresentationRole;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Matter>
 */
class MatterFactory extends Factory
{
    protected $model = Matter::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'title' => fake()->sentence(4),
            'client_id' => Contact::factory()->client(),
            'practice_area' => PracticeArea::CommercialContracts,
            'status' => MatterStatus::Active,
            'stage' => fake()->randomElement(['Drafting', 'Negotiation', 'Review', 'Signed']),
            'opened_at' => now(),
        ];
    }

    public function closed(): static
    {
        return $this->state([
            'status' => MatterStatus::Closed,
            'closed_at' => now(),
        ]);
    }

    public function onHold(): static
    {
        return $this->state([
            'status' => MatterStatus::OnHold,
        ]);
    }

    public function archived(): static
    {
        return $this->state([
            'status' => MatterStatus::Archived,
        ]);
    }

    public function litigation(): static
    {
        return $this->state([
            'is_litigation' => true,
            'litigation_status' => LitigationStatus::Filed,
            'representation_role' => RepresentationRole::Plaintiff,
            'filed_date' => now()->subDays(30),
        ]);
    }
}
