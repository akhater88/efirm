<?php

namespace Database\Factories;

use App\Models\CalendarIntegration;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarIntegration>
 */
class CalendarIntegrationFactory extends Factory
{
    protected $model = CalendarIntegration::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['google', 'outlook']),
            'calendar_id' => fake()->uuid(),
            'oauth_access_token' => fake()->sha256(),
            'oauth_refresh_token' => fake()->sha256(),
            'oauth_expires_at' => now()->addHour(),
            'is_active' => true,
            'last_synced_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
