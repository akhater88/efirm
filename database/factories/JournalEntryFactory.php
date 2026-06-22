<?php

namespace Database\Factories;

use App\Models\JournalEntry;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'entry_number' => 'JE-'.fake()->unique()->numerify('####'),
            'entry_date' => now(),
            'description' => fake()->sentence(),
            'is_posted' => false,
        ];
    }

    public function posted(): static
    {
        return $this->state([
            'is_posted' => true,
            'posted_at' => now(),
        ]);
    }
}
