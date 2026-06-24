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

    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(1),
            'name' => fake()->word().' Plan',
            'name_ar' => 'خطة '.fake()->word(),
            'price_per_seat_usd' => fake()->randomElement([20, 25, 30]),
            'max_seats' => 10,
            'max_matters' => 50,
            'max_contacts' => 100,
            'max_storage_mb' => 5000,
            'features' => ['document_editor', 'clause_library'],
            'is_active' => true,
            'sort_order' => 1,
        ];
    }

    public function unlimited(): static
    {
        return $this->state([
            'max_seats' => null,
            'max_matters' => null,
            'max_contacts' => null,
            'max_storage_mb' => null,
        ]);
    }

    public function starter(): static
    {
        return $this->state([
            'slug' => 'starter',
            'name' => 'Starter',
            'price_per_seat_usd' => 20,
            'max_seats' => 5,
            'max_matters' => 20,
            'max_contacts' => 50,
            'features' => ['document_editor'],
        ]);
    }

    public function pro(): static
    {
        return $this->state([
            'slug' => 'pro',
            'name' => 'Pro',
            'price_per_seat_usd' => 25,
            'max_seats' => 10,
            'max_matters' => 100,
            'max_contacts' => 200,
            'features' => ['document_editor', 'clause_library', 'ai_operations'],
        ]);
    }
}
