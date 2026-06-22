<?php

namespace App\Services;

use App\Models\JournalEntry;

class JournalEntryService
{
    /**
     * Post a journal entry after validating that debits equal credits.
     *
     * @throws \LogicException If debits do not equal credits or entry is already posted
     */
    public function post(JournalEntry $journalEntry): JournalEntry
    {
        if ($journalEntry->is_posted) {
            throw new \LogicException(__('financial.journal_entry_already_posted'));
        }

        $lines = $journalEntry->lines;

        if ($lines->isEmpty()) {
            throw new \LogicException(__('financial.journal_entry_no_lines'));
        }

        $totalDebits = '0.00';
        $totalCredits = '0.00';

        foreach ($lines as $line) {
            $totalDebits = bcadd($totalDebits, (string) $line->debit, 2);
            $totalCredits = bcadd($totalCredits, (string) $line->credit, 2);
        }

        if (bccomp($totalDebits, $totalCredits, 2) !== 0) {
            throw new \LogicException(__('financial.journal_entry_unbalanced'));
        }

        $journalEntry->update([
            'is_posted' => true,
            'posted_at' => now(),
        ]);

        return $journalEntry->fresh();
    }
}
