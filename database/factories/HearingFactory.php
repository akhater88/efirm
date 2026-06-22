<?php

namespace Database\Factories;

use App\Enums\HearingStatus;
use App\Enums\HearingType;
use App\Models\Court;
use App\Models\Hearing;
use App\Models\Matter;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hearing>
 */
class HearingFactory extends Factory
{
    protected $model = Hearing::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'matter_id' => Matter::factory(),
            'hearing_date' => now()->addDays(fake()->numberBetween(1, 60)),
            'court_id' => Court::factory(),
            'hearing_type' => HearingType::FirstSession,
            'status' => HearingStatus::Scheduled,
        ];
    }

    public function held(): static
    {
        return $this->state([
            'status' => HearingStatus::Held,
            'held_at' => now(),
        ]);
    }

    public function postponed(): static
    {
        return $this->state([
            'status' => HearingStatus::Postponed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => HearingStatus::Cancelled,
        ]);
    }
}
