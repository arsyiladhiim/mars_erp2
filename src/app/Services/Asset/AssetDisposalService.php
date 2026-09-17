<?php

namespace App\Services\Asset;

use App\Models\Audit\AuditLog;
use App\Models\Asset\FixedAsset;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\NumberSeries;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\JournalEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Disposes a Fixed Asset with a standard write-off/sale journal entry:
 * Dr Accumulated Depreciation (clears the contra-asset), Dr Cash/Bank (if
 * proceeds were received), Cr Fixed Assets at Cost — with the residual
 * difference booked as a gain or loss on disposal.
 */
class AssetDisposalService
{
    public function dispose(FixedAsset $asset, Carbon $disposalDate, float $proceeds = 0.0, ?int $bankAccountId = null): JournalEntry
    {
        if ($asset->status === 'disposed') {
            throw new RuntimeException("Asset {$asset->code} has already been disposed.");
        }

        if ($proceeds > 0 && ! $bankAccountId) {
            throw new RuntimeException('Select a bank/cash account to receive the disposal proceeds.');
        }

        AccountingPeriod::assertOpenForPosting($disposalDate, $asset->company_id);

        return DB::transaction(function () use ($asset, $disposalDate, $proceeds, $bankAccountId) {
            $accumulatedDepreciation = round((float) $asset->accumulated_depreciation, 2);
            $acquisitionCost = round((float) $asset->acquisition_cost, 2);
            $gainLoss = round(($accumulatedDepreciation + $proceeds) - $acquisitionCost, 2);

            $journalEntry = $this->postJournalEntry($asset, $disposalDate, $proceeds, $bankAccountId, $accumulatedDepreciation, $acquisitionCost, $gainLoss);

            $asset->forceFill(['status' => 'disposed'])->save();

            AuditLog::record('disposed', FixedAsset::class, $asset->getKey(), null, [
                'disposal_date' => $disposalDate->toDateString(),
                'proceeds' => $proceeds,
                'book_value' => round($acquisitionCost - $accumulatedDepreciation, 2),
                'gain_loss' => $gainLoss,
                'journal_entry_id' => $journalEntry->id,
            ]);

            return $journalEntry;
        });
    }

    protected function postJournalEntry(
        FixedAsset $asset,
        Carbon $disposalDate,
        float $proceeds,
        ?int $bankAccountId,
        float $accumulatedDepreciation,
        float $acquisitionCost,
        float $gainLoss,
    ): JournalEntry {
        $fixedAssetsAccount = GlAccountMapping::resolve('fixed_assets', $asset->company_id);
        $accumulatedAccount = GlAccountMapping::resolve('accumulated_depreciation', $asset->company_id);
        $period = AccountingPeriod::forDate($disposalDate, $asset->company_id);
        $totalDebit = round($accumulatedDepreciation + $proceeds + max(0, -$gainLoss), 2);

        $journalEntry = JournalEntry::create([
            'company_id' => $asset->company_id,
            'accounting_period_id' => $period?->id,
            'number' => NumberSeries::next($asset->company_id, 'journal_entry'),
            'entry_date' => $disposalDate->toDateString(),
            'source_type' => 'manual',
            'reference_type' => FixedAsset::class,
            'reference_id' => $asset->id,
            'memo' => "Disposal of asset {$asset->code} — {$asset->name}",
            'total_debit' => $totalDebit,
            'total_credit' => $totalDebit,
            'status' => 'posted',
        ]);

        $lines = [
            ['chart_of_account_id' => $accumulatedAccount->id, 'description' => "Disposal {$asset->code} — Accumulated Depreciation", 'debit' => $accumulatedDepreciation, 'credit' => 0],
        ];

        if ($proceeds > 0) {
            $cashAccount = \App\Models\Finance\BankAccount::query()->findOrFail($bankAccountId)->chartOfAccount()->firstOrFail();
            $lines[] = ['chart_of_account_id' => $cashAccount->id, 'description' => "Disposal {$asset->code} — Proceeds", 'debit' => $proceeds, 'credit' => 0];
        }

        $lines[] = ['chart_of_account_id' => $fixedAssetsAccount->id, 'description' => "Disposal {$asset->code} — Fixed Assets at Cost", 'debit' => 0, 'credit' => $acquisitionCost];

        if (abs($gainLoss) >= 0.01) {
            $gainLossAccount = GlAccountMapping::resolve('asset_disposal_gain_loss', $asset->company_id);

            $lines[] = $gainLoss > 0
                ? ['chart_of_account_id' => $gainLossAccount->id, 'description' => "Disposal {$asset->code} — Gain on Disposal", 'debit' => 0, 'credit' => $gainLoss]
                : ['chart_of_account_id' => $gainLossAccount->id, 'description' => "Disposal {$asset->code} — Loss on Disposal", 'debit' => abs($gainLoss), 'credit' => 0];
        }

        $journalEntry->lines()->createMany($lines);

        return $journalEntry;
    }
}
