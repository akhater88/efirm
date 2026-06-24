<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(2),
            'name' => $this->faker->words(2, true),
            'name_ar' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'description_ar' => $this->faker->sentence(),
            'price_per_seat_usd' => $this->faker->randomElement([20.00, 25.00, 30.00]),
            'max_seats' => $this->faker->numberBetween(5, 50),
            'max_matters' => $this->faker->numberBetween(50, 500),
            'max_contacts' => $this->faker->numberBetween(100, 1000),
            'max_storage_mb' => $this->faker->numberBetween(1024, 10240),
            'features' => [],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
