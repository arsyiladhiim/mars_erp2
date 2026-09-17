<?php

namespace App\Services\Reporting;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Balance Sheet as of a date: every account with posted activity up to that
 * date, netted onto its normal-balance side (asset/expense/cogs = debit
 * normal, liability/equity/revenue = credit normal), grouped by type.
 * Assets should equal Liabilities + Equity when the ledger is sound —
 * callers can use that as a sanity check.
 */
class BalanceSheetService
{
    public function generate(int $companyId, string $asOfDate): array
    {
        $rows = DB::table('finance_journal_entry_lines as l')
            ->join('finance_journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->join('finance_chart_of_accounts as a', 'a.id', '=', 'l.chart_of_account_id')
            ->where('e.company_id', $companyId)
            ->where('e.status', 'posted')
            ->whereDate('e.entry_date', '<=', $asOfDate)
            ->whereIn('a.account_type', ['asset', 'liability', 'equity'])
            ->groupBy('a.id', 'a.code', 'a.name', 'a.account_type')
            ->havingRaw('SUM(l.debit) <> 0 OR SUM(l.credit) <> 0')
            ->select([
                'a.id', 'a.code', 'a.name', 'a.account_type',
                DB::raw('SUM(l.debit) as total_debit'),
                DB::raw('SUM(l.credit) as total_credit'),
            ])
            ->orderBy('a.code')
            ->get();

        $accounts = collect($rows)->map(function ($row) {
            $isDebitNormal = $row->account_type === 'asset';
            $balance = $isDebitNormal
                ? (float) $row->total_debit - (float) $row->total_credit
                : (float) $row->total_credit - (float) $row->total_debit;

            return [
                'code' => $row->code,
                'name' => $row->name,
                'account_type' => $row->account_type,
                'balance' => round($balance, 2),
            ];
        });

        $assets = $this->sumFor($accounts, 'asset');
        $liabilities = $this->sumFor($accounts, 'liability');
        $equity = $this->sumFor($accounts, 'equity');

        return [
            'accounts' => $accounts->values(),
            'total_assets' => $assets,
            'total_liabilities' => $liabilities,
            'total_equity' => $equity,
            'total_liabilities_and_equity' => round($liabilities + $equity, 2),
        ];
    }

    protected function sumFor(Collection $accounts, string $type): float
    {
        return round($accounts->where('account_type', $type)->sum('balance'), 2);
    }
}
