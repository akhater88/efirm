<?php

namespace Database\Factories;

use App\Models\Court;
use App\Models\Judge;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Judge>
 */
class JudgeFactory extends Factory
{
    protected $model = Judge::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name_ar' => 'القاضي '.fake()->lastName(),
            'name_en' => 'Judge '.fake()->lastName(),
            'court_id' => null,
        ];
    }

    public function forCourt(Court $court): static
    {
        return $this->state([
            'court_id' => $court->id,
            'workspace_id' => $court->workspace_id,
        ]);
    }
}
