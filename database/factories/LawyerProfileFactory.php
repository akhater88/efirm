<?php

namespace Database\Factories;

use App\Enums\LawyerProfileStatus;
use App\Models\LawyerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LawyerProfile>
 */
class LawyerProfileFactory extends Factory
{
    protected $model = LawyerProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => LawyerProfileStatus::Active,
            'bar_admission_country' => 'JO',
            'practice_areas' => ['commercial_contracts'],
            'languages_spoken' => ['ar', 'en'],
            'default_hourly_rate' => '150.00',
            'default_currency' => 'USD',
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => LawyerProfileStatus::Inactive]);
    }

    public function onLeave(): static
    {
        return $this->state(['status' => LawyerProfileStatus::OnLeave]);
    }
}
