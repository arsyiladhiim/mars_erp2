<?php

namespace App\Services\Finance;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Posted transaction history for a single Chart of Account within a date
 * range, with a running balance (debit adds, credit subtracts — correct
 * direction for debit-normal accounts; credit-normal accounts simply carry
 * a negative running balance, which the presentation layer can flip).
 */
class GeneralLedgerService
{
    public function forAccount(int $chartOfAccountId, string $from, string $to): Collection
    {
        $lines = DB::table('finance_journal_entry_lines as l')
            ->join('finance_journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->where('l.chart_of_account_id', $chartOfAccountId)
            ->where('e.status', 'posted')
            ->whereDate('e.entry_date', '>=', $from)
            ->whereDate('e.entry_date', '<=', $to)
            ->orderBy('e.entry_date')
            ->orderBy('e.id')
            ->select(['e.entry_date', 'e.number', 'e.memo', 'l.description', 'l.debit', 'l.credit'])
            ->get();

        $runningBalance = 0.0;

        return $lines->map(function ($line) use (&$runningBalance) {
            $runningBalance += (float) $line->debit - (float) $line->credit;

            return [
                'entry_date' => $line->entry_date,
                'number' => $line->number,
                'memo' => $line->memo,
                'description' => $line->description,
                'debit' => round((float) $line->debit, 2),
                'credit' => round((float) $line->credit, 2),
                'running_balance' => round($runningBalance, 2),
            ];
        })->values();
    }
}
