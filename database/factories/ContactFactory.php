<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'type' => 'person',
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'is_client' => false,
            'is_counterparty' => false,
        ];
    }

    public function organization(): static
    {
        return $this->state([
            'type' => 'organization',
            'first_name' => null,
            'middle_name' => null,
            'last_name' => null,
            'organization_name' => fake()->company(),
        ]);
    }

    public function client(): static
    {
        return $this->state(['is_client' => true]);
    }

    public function counterparty(): static
    {
        return $this->state(['is_counterparty' => true]);
    }
}
