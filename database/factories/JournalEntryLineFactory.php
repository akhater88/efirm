<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalEntryLine>
 */
class JournalEntryLineFactory extends Factory
{
    protected $model = JournalEntryLine::class;

    public function definition(): array
    {
        return [
            'journal_entry_id' => JournalEntry::factory(),
            'account_id' => Account::factory(),
            'debit' => '0.00',
            'credit' => '0.00',
        ];
    }

    public function debit(string $amount = '1000.00'): static
    {
        return $this->state([
            'debit' => $amount,
            'credit' => '0.00',
        ]);
    }

    public function credit(string $amount = '1000.00'): static
    {
        return $this->state([
            'debit' => '0.00',
            'credit' => $amount,
        ]);
    }
}
