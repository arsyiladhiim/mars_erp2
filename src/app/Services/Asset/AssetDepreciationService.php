<?php

namespace App\Services\Asset;

use App\Models\Asset\AssetDepreciationEntry;
use App\Models\Asset\FixedAsset;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\NumberSeries;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\JournalEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Monthly straight-line depreciation for every eligible asset in a company,
 * aggregated into a single Dr Depreciation Expense / Cr Accumulated
 * Depreciation journal entry per run (one JE per company per month, not
 * one per asset — matches how Stock Adjustment/Opname aggregate their
 * variance postings). Declining-balance assets are left untouched: the
 * schema allows the method but no calculation for it exists yet.
 */
class AssetDepreciationService
{
    /**
     * @return JournalEntry|null null when no asset had any depreciation due this period.
     */
    public function runForCompany(int $companyId, Carbon $periodDate): ?JournalEntry
    {
        // Normalized here (not left to callers) so idempotency — one run per
        // calendar month per asset — holds regardless of which day within
        // the month a caller passes in.
        $periodDate = $periodDate->copy()->endOfMonth();

        AccountingPeriod::assertOpenForPosting($periodDate, $companyId);

        return DB::transaction(function () use ($companyId, $periodDate) {
            $assets = FixedAsset::query()
                ->where('company_id', $companyId)
                ->where('depreciation_method', 'straight_line')
                ->whereNotIn('status', ['draft', 'disposed'])
                ->get();

            $totalDepreciation = 0.0;

            foreach ($assets as $asset) {
                $amount = $this->depreciateAsset($asset, $periodDate);
                $totalDepreciation += $amount;
            }

            if ($totalDepreciation <= 0) {
                return null;
            }

            return $this->postJournalEntry($companyId, $periodDate, round($totalDepreciation, 2));
        });
    }

    /**
     * @return float the amount actually depreciated this period (0 if skipped).
     */
    protected function depreciateAsset(FixedAsset $asset, Carbon $periodDate): float
    {
        $alreadyRun = $asset->depreciationEntries()
            ->whereDate('period_date', $periodDate->toDateString())
            ->exists();

        if ($alreadyRun) {
            return 0.0;
        }

        $depreciableBase = round((float) $asset->acquisition_cost - (float) $asset->residual_value, 2);
        $remaining = round($depreciableBase - (float) $asset->accumulated_depreciation, 2);

        if ($remaining <= 0 || $asset->useful_life_months <= 0) {
            return 0.0;
        }

        $monthlyAmount = round($depreciableBase / $asset->useful_life_months, 2);
        $amount = min($monthlyAmount, $remaining);
        $newAccumulated = round((float) $asset->accumulated_depreciation + $amount, 2);

        AssetDepreciationEntry::create([
            'fixed_asset_id' => $asset->id,
            'period_date' => $periodDate->toDateString(),
            'depreciation_amount' => $amount,
            'accumulated_after' => $newAccumulated,
            'book_value_after' => round((float) $asset->acquisition_cost - $newAccumulated, 2),
            'status' => 'posted',
        ]);

        $asset->forceFill(['accumulated_depreciation' => $newAccumulated])->save();

        return $amount;
    }

    protected function postJournalEntry(int $companyId, Carbon $periodDate, float $amount): JournalEntry
    {
        $expenseAccount = GlAccountMapping::resolve('depreciation_expense', $companyId);
        $accumulatedAccount = GlAccountMapping::resolve('accumulated_depreciation', $companyId);
        $period = AccountingPeriod::forDate($periodDate, $companyId);
        $label = $periodDate->format('F Y');

        $journalEntry = JournalEntry::create([
            'company_id' => $companyId,
            'accounting_period_id' => $period?->id,
            'number' => NumberSeries::next($companyId, 'journal_entry'),
            'entry_date' => $periodDate->toDateString(),
            'source_type' => 'depreciation',
            'memo' => "Monthly depreciation — {$label}",
            'total_debit' => $amount,
            'total_credit' => $amount,
            'status' => 'posted',
        ]);

        $journalEntry->lines()->createMany([
            ['chart_of_account_id' => $expenseAccount->id, 'description' => "Depreciation Expense — {$label}", 'debit' => $amount, 'credit' => 0],
            ['chart_of_account_id' => $accumulatedAccount->id, 'description' => "Accumulated Depreciation — {$label}", 'debit' => 0, 'credit' => $amount],
        ]);

        return $journalEntry;
    }
}
