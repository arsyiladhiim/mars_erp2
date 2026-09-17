<?php

namespace App\Services\Finance;

use App\Models\Audit\AuditLog;
use App\Models\Core\AccountingPeriod;
use App\Models\Finance\JournalEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Closes/reopens an Accounting Period (PRD §11.7, Key Business Rule #5).
 * Closing is blocked while any draft Journal Entry still falls inside the
 * period's date range — those must be posted or removed first, otherwise
 * they'd become permanently unpostable once the period locks.
 */
class PeriodClosingService
{
    public function close(AccountingPeriod $period, User $user): AccountingPeriod
    {
        if ($period->isLocked()) {
            throw new RuntimeException("Period \"{$period->name}\" is already {$period->status}.");
        }

        $period->loadMissing('fiscalYear');
        $companyId = $period->fiscalYear->company_id;

        $draftCount = JournalEntry::query()
            ->where('company_id', $companyId)
            ->where('status', 'draft')
            ->whereDate('entry_date', '>=', $period->start_date)
            ->whereDate('entry_date', '<=', $period->end_date)
            ->count();

        if ($draftCount > 0) {
            throw new RuntimeException("Cannot close \"{$period->name}\": {$draftCount} draft journal entr".($draftCount === 1 ? 'y' : 'ies')." still fall within this period. Post or remove them first.");
        }

        return DB::transaction(function () use ($period, $user) {
            $period->forceFill([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => $user->id,
            ])->save();

            AuditLog::record('closed', AccountingPeriod::class, $period->getKey());

            return $period;
        });
    }

    public function reopen(AccountingPeriod $period, User $user): AccountingPeriod
    {
        if ($period->status === 'locked') {
            throw new RuntimeException("Period \"{$period->name}\" is locked and cannot be reopened.");
        }

        if ($period->status !== 'closed') {
            throw new RuntimeException("Period \"{$period->name}\" is not closed.");
        }

        return DB::transaction(function () use ($period, $user) {
            $period->forceFill([
                'status' => 'open',
                'closed_at' => null,
                'closed_by' => null,
            ])->save();

            AuditLog::record('reopened', AccountingPeriod::class, $period->getKey(), null, ['by' => $user->id]);

            return $period;
        });
    }
}
