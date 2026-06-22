<?php

namespace Database\Factories;

use App\Enums\TrustLedgerEntryType;
use App\Models\TrustAccount;
use App\Models\TrustLedgerEntry;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrustLedgerEntry>
 */
class TrustLedgerEntryFactory extends Factory
{
    protected $model = TrustLedgerEntry::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'trust_account_id' => TrustAccount::factory(),
            'type' => TrustLedgerEntryType::Deposit,
            'amount' => '1000.00',
            'balance_after' => '1000.00',
            'description' => fake()->sentence(),
            'created_at' => now(),
        ];
    }

    public function deposit(): static
    {
        return $this->state(['type' => TrustLedgerEntryType::Deposit]);
    }

    public function withdrawal(): static
    {
        return $this->state(['type' => TrustLedgerEntryType::Withdrawal]);
    }
}
