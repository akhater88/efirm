<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\TrustAccount;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrustAccount>
 */
class TrustAccountFactory extends Factory
{
    protected $model = TrustAccount::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'contact_id' => Contact::factory()->client(),
            'name' => fake()->words(3, true).' Trust',
            'bank_name' => fake()->company(),
            'currency' => 'USD',
            'balance' => '0.00',
        ];
    }
}
