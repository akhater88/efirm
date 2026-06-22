<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'code' => fake()->unique()->numerify('####'),
            'name' => fake()->words(2, true),
            'account_type' => AccountType::Asset,
            'is_system' => false,
        ];
    }

    public function system(): static
    {
        return $this->state(['is_system' => true]);
    }

    public function asset(): static
    {
        return $this->state(['account_type' => AccountType::Asset]);
    }

    public function liability(): static
    {
        return $this->state(['account_type' => AccountType::Liability]);
    }

    public function revenue(): static
    {
        return $this->state(['account_type' => AccountType::Revenue]);
    }

    public function expense(): static
    {
        return $this->state(['account_type' => AccountType::Expense]);
    }
}
