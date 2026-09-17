<?php

namespace App\Services\Finance;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Unadjusted Trial Balance: for every account with posted activity up to
 * asOfDate, the raw sum of debits and the raw sum of credits (not netted to
 * the account's normal-balance side). Because every posted Journal Entry is
 * itself balanced, the two grand totals are guaranteed equal — this doubles
 * as a running sanity check on the ledger.
 */
class TrialBalanceService
{
    public function generate(int $companyId, string $asOfDate): Collection
    {
        $rows = DB::table('finance_journal_entry_lines as l')
            ->join('finance_journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->join('finance_chart_of_accounts as a', 'a.id', '=', 'l.chart_of_account_id')
            ->where('e.company_id', $companyId)
            ->where('e.status', 'posted')
            ->whereDate('e.entry_date', '<=', $asOfDate)
            ->groupBy('a.id', 'a.code', 'a.name', 'a.account_type')
            ->orderBy('a.code')
            ->select([
                'a.id', 'a.code', 'a.name', 'a.account_type',
                DB::raw('SUM(l.debit) as total_debit'),
                DB::raw('SUM(l.credit) as total_credit'),
            ])
            ->havingRaw('SUM(l.debit) <> 0 OR SUM(l.credit) <> 0')
            ->get();

        return collect($rows)->map(fn ($row) => [
            'id' => $row->id,
            'code' => $row->code,
            'name' => $row->name,
            'account_type' => $row->account_type,
            'debit' => round((float) $row->total_debit, 2),
            'credit' => round((float) $row->total_credit, 2),
        ])->values();
    }
}
