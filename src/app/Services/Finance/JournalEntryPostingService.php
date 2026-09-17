<?php

namespace App\Services\Finance;

use App\Models\Audit\AuditLog;
use App\Models\Core\AccountingPeriod;
use App\Models\Finance\JournalEntry;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Posts a manually-created, draft Journal Entry (PRD §11.2, Key Business
 * Rule #2: a journal entry must balance before it can post). The header's
 * total_debit/total_credit are derived from the lines here — the form never
 * lets a user set them directly — so this is the single point where an
 * unbalanced entry is caught before status can become 'posted'.
 */
class JournalEntryPostingService
{
    public function post(JournalEntry $journalEntry): JournalEntry
    {
        if ($journalEntry->status !== 'draft') {
            throw new RuntimeException("Journal Entry {$journalEntry->number} has already been posted.");
        }

        $journalEntry->loadMissing('lines');

        if ($journalEntry->lines->isEmpty()) {
            throw new RuntimeException('Cannot post a Journal Entry with no lines.');
        }

        $totalDebit = $journalEntry->lines->sum(fn ($line) => (float) $line->debit);
        $totalCredit = $journalEntry->lines->sum(fn ($line) => (float) $line->credit);

        if (bccomp((string) round($totalDebit, 2), (string) round($totalCredit, 2), 2) !== 0) {
            throw new RuntimeException("Journal Entry does not balance: total debit {$totalDebit} vs total credit {$totalCredit}.");
        }

        AccountingPeriod::assertOpenForPosting($journalEntry->entry_date, $journalEntry->company_id);

        return DB::transaction(function () use ($journalEntry, $totalDebit, $totalCredit) {
            $journalEntry->forceFill([
                'total_debit' => round($totalDebit, 2),
                'total_credit' => round($totalCredit, 2),
                'status' => 'posted',
            ])->save();

            AuditLog::record('posted', JournalEntry::class, $journalEntry->getKey());

            return $journalEntry;
        });
    }
}
