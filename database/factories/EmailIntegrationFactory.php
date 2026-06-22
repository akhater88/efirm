<?php

namespace Database\Factories;

use App\Models\EmailIntegration;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailIntegration>
 */
class EmailIntegrationFactory extends Factory
{
    protected $model = EmailIntegration::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['outlook', 'gmail']),
            'email_address' => fake()->unique()->safeEmail(),
            'oauth_access_token' => fake()->sha256(),
            'oauth_refresh_token' => fake()->sha256(),
            'oauth_expires_at' => now()->addHour(),
            'scopes_granted' => ['mail.read', 'mail.send'],
            'is_active' => true,
            'last_synced_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
