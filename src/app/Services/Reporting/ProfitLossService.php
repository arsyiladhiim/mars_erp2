<?php

namespace App\Services\Reporting;

use Illuminate\Support\Facades\DB;

/**
 * P&L (Income Statement) for a date range: Revenue - COGS = Gross Profit;
 * Gross Profit - Operating Expenses = Net Profit. Revenue/expense-type
 * accounts are credit-normal/debit-normal respectively, so each is netted
 * on its own normal side rather than raw debit/credit like Trial Balance.
 */
class ProfitLossService
{
    public function generate(int $companyId, string $from, string $to): array
    {
        $revenue = $this->netByAccountTypes($companyId, $from, $to, ['revenue'], creditNormal: true);
        $cogs = $this->netByAccountTypes($companyId, $from, $to, ['cogs'], creditNormal: false);
        $expenses = $this->netByAccountTypes($companyId, $from, $to, ['expense'], creditNormal: false);

        $grossProfit = round($revenue - $cogs, 2);
        $netProfit = round($grossProfit - $expenses, 2);

        return [
            'revenue' => round($revenue, 2),
            'cogs' => round($cogs, 2),
            'gross_profit' => $grossProfit,
            'expenses' => round($expenses, 2),
            'net_profit' => $netProfit,
        ];
    }

    /**
     * @param  string[]  $accountTypes
     */
    protected function netByAccountTypes(int $companyId, string $from, string $to, array $accountTypes, bool $creditNormal): float
    {
        $row = DB::table('finance_journal_entry_lines as l')
            ->join('finance_journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->join('finance_chart_of_accounts as a', 'a.id', '=', 'l.chart_of_account_id')
            ->where('e.company_id', $companyId)
            ->where('e.status', 'posted')
            ->whereDate('e.entry_date', '>=', $from)
            ->whereDate('e.entry_date', '<=', $to)
            ->whereIn('a.account_type', $accountTypes)
            ->selectRaw('COALESCE(SUM(l.debit), 0) as total_debit, COALESCE(SUM(l.credit), 0) as total_credit')
            ->first();

        $debit = (float) ($row->total_debit ?? 0);
        $credit = (float) ($row->total_credit ?? 0);

        return $creditNormal ? ($credit - $debit) : ($debit - $credit);
    }
}
