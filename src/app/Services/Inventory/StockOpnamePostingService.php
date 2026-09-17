<?php

namespace App\Services\Inventory;

use App\Models\Audit\AuditLog;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\NumberSeries;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\JournalEntry;
use App\Models\Inventory\StockOpname;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Posts a draft/counting Stock Opname: for each line, reconciles the
 * physical count against the *live* ledger balance at posting time (not
 * whatever was on hand when the line was entered — a stale snapshot could
 * otherwise silently mis-book the variance), records the variance, and
 * posts any non-zero net value change the same way a Stock Adjustment does.
 */
class StockOpnamePostingService
{
    public function __construct(protected StockLedgerService $stockLedger) {}

    public function post(StockOpname $opname): StockOpname
    {
        if (in_array($opname->status, ['posted', 'cancelled'], true)) {
            throw new RuntimeException("Stock Opname {$opname->number} has already been posted.");
        }

        $opname->loadMissing('lines');

        if ($opname->lines->isEmpty()) {
            throw new RuntimeException('Cannot post a Stock Opname with no lines.');
        }

        AccountingPeriod::assertOpenForPosting($opname->count_date, $opname->company_id);

        return DB::transaction(function () use ($opname) {
            $netValueChange = 0.0;

            foreach ($opname->lines as $line) {
                $systemQuantity = $this->stockLedger->currentBalance($line->item_id, $opname->warehouse_id)['quantity'];
                $variance = round((float) $line->physical_quantity - $systemQuantity, 4);

                $line->update(['system_quantity' => $systemQuantity, 'variance_quantity' => $variance]);

                if ($variance === 0.0) {
                    continue;
                }

                $entry = $this->stockLedger->adjust(
                    itemId: $line->item_id,
                    warehouseId: $opname->warehouse_id,
                    signedQuantity: $variance,
                    source: $line,
                    movementDate: $opname->count_date,
                    movementType: 'stock_opname',
                );

                $netValueChange += $entry->quantity_in > 0
                    ? (float) $entry->quantity_in * (float) $entry->unit_cost
                    : -((float) $entry->quantity_out * (float) $entry->unit_cost);
            }

            if (abs($netValueChange) >= 0.01) {
                $this->postJournalEntry($opname, round($netValueChange, 2));
            }

            $opname->forceFill(['status' => 'posted'])->save();

            AuditLog::record('posted', StockOpname::class, $opname->getKey(), null, ['net_value_change' => $netValueChange]);

            return $opname;
        });
    }

    protected function postJournalEntry(StockOpname $opname, float $netValueChange): JournalEntry
    {
        $inventoryAccount = GlAccountMapping::resolve('inventory', $opname->company_id);
        $adjustmentAccount = GlAccountMapping::resolve('inventory_adjustment', $opname->company_id);
        $period = AccountingPeriod::forDate($opname->count_date, $opname->company_id);
        $amount = abs($netValueChange);

        $journalEntry = JournalEntry::create([
            'company_id' => $opname->company_id,
            'accounting_period_id' => $period?->id,
            'number' => NumberSeries::next($opname->company_id, 'journal_entry'),
            'entry_date' => $opname->count_date,
            'source_type' => 'stock_adjustment',
            'reference_type' => StockOpname::class,
            'reference_id' => $opname->id,
            'memo' => "Stock Opname {$opname->number}",
            'total_debit' => $amount,
            'total_credit' => $amount,
            'status' => 'posted',
        ]);

        $lines = $netValueChange > 0
            ? [
                ['chart_of_account_id' => $inventoryAccount->id, 'description' => "OPN {$opname->number} — Inventory", 'debit' => $amount, 'credit' => 0],
                ['chart_of_account_id' => $adjustmentAccount->id, 'description' => "OPN {$opname->number} — Inventory Adjustment Gain", 'debit' => 0, 'credit' => $amount],
            ]
            : [
                ['chart_of_account_id' => $adjustmentAccount->id, 'description' => "OPN {$opname->number} — Inventory Adjustment Loss", 'debit' => $amount, 'credit' => 0],
                ['chart_of_account_id' => $inventoryAccount->id, 'description' => "OPN {$opname->number} — Inventory", 'debit' => 0, 'credit' => $amount],
            ];

        $journalEntry->lines()->createMany($lines);

        return $journalEntry;
    }
}
