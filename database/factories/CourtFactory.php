<?php

namespace Database\Factories;

use App\Enums\CourtType;
use App\Models\Court;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Court>
 */
class CourtFactory extends Factory
{
    protected $model = Court::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name_ar' => 'محكمة '.fake()->city(),
            'name_en' => fake()->city().' Court',
            'court_type' => CourtType::FirstInstance,
            'jurisdiction_country' => 'JO',
            'city' => fake()->city(),
        ];
    }
}
