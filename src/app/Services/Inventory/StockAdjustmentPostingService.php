<?php

namespace App\Services\Inventory;

use App\Models\Audit\AuditLog;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\NumberSeries;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\JournalEntry;
use App\Models\Inventory\StockAdjustment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Posts a draft Stock Adjustment: a signed ledger entry per line (positive =
 * found/increase, negative = loss/decrease) and a single aggregated journal
 * entry for the net inventory value change (Dr/Cr Inventory vs the
 * Inventory Adjustment Gain/Loss account).
 */
class StockAdjustmentPostingService
{
    public function __construct(protected StockLedgerService $stockLedger) {}

    public function post(StockAdjustment $adjustment): StockAdjustment
    {
        if ($adjustment->status !== 'draft') {
            throw new RuntimeException("Stock Adjustment {$adjustment->number} has already been posted.");
        }

        $adjustment->loadMissing('lines');

        if ($adjustment->lines->isEmpty()) {
            throw new RuntimeException('Cannot post a Stock Adjustment with no lines.');
        }

        AccountingPeriod::assertOpenForPosting($adjustment->adjustment_date, $adjustment->company_id);

        return DB::transaction(function () use ($adjustment) {
            $netValueChange = 0.0;

            foreach ($adjustment->lines as $line) {
                if ((float) $line->quantity === 0.0) {
                    continue;
                }

                $entry = $this->stockLedger->adjust(
                    itemId: $line->item_id,
                    warehouseId: $adjustment->warehouse_id,
                    signedQuantity: (float) $line->quantity,
                    source: $line,
                    movementDate: $adjustment->adjustment_date,
                    unitCost: (float) $line->unit_cost,
                    movementType: 'adjustment',
                );

                $netValueChange += $entry->quantity_in > 0
                    ? (float) $entry->quantity_in * (float) $entry->unit_cost
                    : -((float) $entry->quantity_out * (float) $entry->unit_cost);
            }

            if (abs($netValueChange) >= 0.01) {
                $this->postJournalEntry($adjustment, round($netValueChange, 2));
            }

            $adjustment->forceFill(['status' => 'posted'])->save();

            AuditLog::record('posted', StockAdjustment::class, $adjustment->getKey(), null, ['net_value_change' => $netValueChange]);

            return $adjustment;
        });
    }

    protected function postJournalEntry(StockAdjustment $adjustment, float $netValueChange): JournalEntry
    {
        $inventoryAccount = GlAccountMapping::resolve('inventory', $adjustment->company_id);
        $adjustmentAccount = GlAccountMapping::resolve('inventory_adjustment', $adjustment->company_id);
        $period = AccountingPeriod::forDate($adjustment->adjustment_date, $adjustment->company_id);
        $amount = abs($netValueChange);

        $journalEntry = JournalEntry::create([
            'company_id' => $adjustment->company_id,
            'accounting_period_id' => $period?->id,
            'number' => NumberSeries::next($adjustment->company_id, 'journal_entry'),
            'entry_date' => $adjustment->adjustment_date,
            'source_type' => 'stock_adjustment',
            'reference_type' => StockAdjustment::class,
            'reference_id' => $adjustment->id,
            'memo' => "Stock Adjustment {$adjustment->number}",
            'total_debit' => $amount,
            'total_credit' => $amount,
            'status' => 'posted',
        ]);

        $lines = $netValueChange > 0
            ? [
                ['chart_of_account_id' => $inventoryAccount->id, 'description' => "ADJ {$adjustment->number} — Inventory", 'debit' => $amount, 'credit' => 0],
                ['chart_of_account_id' => $adjustmentAccount->id, 'description' => "ADJ {$adjustment->number} — Inventory Adjustment Gain", 'debit' => 0, 'credit' => $amount],
            ]
            : [
                ['chart_of_account_id' => $adjustmentAccount->id, 'description' => "ADJ {$adjustment->number} — Inventory Adjustment Loss", 'debit' => $amount, 'credit' => 0],
                ['chart_of_account_id' => $inventoryAccount->id, 'description' => "ADJ {$adjustment->number} — Inventory", 'debit' => 0, 'credit' => $amount],
            ];

        $journalEntry->lines()->createMany($lines);

        return $journalEntry;
    }
}
