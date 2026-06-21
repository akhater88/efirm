<?php

namespace Database\Factories;

use App\Enums\ObligationStatus;
use App\Enums\ObligationType;
use App\Enums\ResponsibleParty;
use App\Models\Document;
use App\Models\Obligation;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Obligation>
 */
class ObligationFactory extends Factory
{
    protected $model = Obligation::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'document_id' => Document::factory(),
            'title' => fake()->sentence(4),
            'obligation_type' => ObligationType::Payment,
            'responsible_party' => ResponsibleParty::Us,
            'due_date' => now()->addDays(fake()->numberBetween(7, 90)),
            'status' => ObligationStatus::Pending,
        ];
    }

    public function overdue(): static
    {
        return $this->state([
            'due_date' => now()->subDays(5),
            'status' => ObligationStatus::Overdue,
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status' => ObligationStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
