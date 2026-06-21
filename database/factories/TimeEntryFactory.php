<?php

namespace Database\Factories;

use App\Models\Matter;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-30 days', 'now');
        $durationMinutes = fake()->numberBetween(15, 480);

        return [
            'workspace_id' => Workspace::factory(),
            'user_id' => User::factory(),
            'matter_id' => Matter::factory(),
            'description' => fake()->sentence(),
            'duration_minutes' => $durationMinutes,
            'started_at' => $startedAt,
            'ended_at' => (clone $startedAt)->modify("+{$durationMinutes} minutes"),
            'is_billable' => true,
            'billing_rate_per_hour' => '150.00',
            'currency' => 'USD',
        ];
    }

    public function nonBillable(): static
    {
        return $this->state([
            'is_billable' => false,
            'billing_rate_per_hour' => null,
        ]);
    }
}
