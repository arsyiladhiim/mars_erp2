<?php

namespace App\Services\Sales;

use App\Models\Audit\AuditLog;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\NumberSeries;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\JournalEntry;
use App\Models\Sales\Delivery;
use App\Models\Sales\SalesOrder;
use App\Services\Inventory\StockLedgerService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Posts a draft Delivery: stock-out per line via StockLedgerService::issue()
 * (costed at the item's current moving average), a balanced COGS journal
 * (Dr COGS / Cr Inventory), and rolls delivered quantity + status back up
 * to the source Sales Order.
 */
class DeliveryPostingService
{
    public function __construct(protected StockLedgerService $stockLedger) {}

    public function post(Delivery $delivery): JournalEntry
    {
        if ($delivery->status !== 'draft') {
            throw new RuntimeException("Delivery {$delivery->number} has already been posted.");
        }

        $delivery->loadMissing('lines.salesOrderLine', 'salesOrder.lines');

        if ($delivery->lines->isEmpty()) {
            throw new RuntimeException('Cannot post a Delivery with no lines.');
        }

        if ($delivery->salesOrder && in_array($delivery->salesOrder->status, ['draft', 'pending_approval', 'rejected', 'cancelled'], true)) {
            throw new RuntimeException("Sales Order {$delivery->salesOrder->number} must be approved before it can be delivered.");
        }

        AccountingPeriod::assertOpenForPosting($delivery->delivery_date, $delivery->company_id);

        return DB::transaction(function () use ($delivery) {
            $totalCost = 0.0;

            foreach ($delivery->lines as $line) {
                $entry = $this->stockLedger->issue(
                    itemId: $line->item_id,
                    warehouseId: $delivery->warehouse_id,
                    quantity: (float) $line->quantity,
                    source: $line,
                    movementDate: $delivery->delivery_date,
                    batchNumber: $line->batch_number,
                    movementType: 'sales_delivery',
                );

                $totalCost += (float) $entry->quantity_out * (float) $entry->unit_cost;

                if ($line->salesOrderLine) {
                    $line->salesOrderLine->increment('delivered_quantity', $line->quantity);
                }
            }

            $journalEntry = $this->postJournalEntry($delivery, round($totalCost, 2));

            if ($delivery->salesOrder) {
                $this->refreshSalesOrderStatus($delivery->salesOrder);
            }

            $delivery->forceFill(['status' => 'posted'])->save();

            AuditLog::record('posted', Delivery::class, $delivery->getKey(), null, [
                'journal_entry_id' => $journalEntry->id,
                'total_cost' => $totalCost,
            ]);

            return $journalEntry;
        });
    }

    protected function postJournalEntry(Delivery $delivery, float $totalCost): JournalEntry
    {
        $cogsAccount = GlAccountMapping::resolve('cogs', $delivery->company_id);
        $inventoryAccount = GlAccountMapping::resolve('inventory', $delivery->company_id);
        $period = AccountingPeriod::forDate($delivery->delivery_date, $delivery->company_id);

        $journalEntry = JournalEntry::create([
            'company_id' => $delivery->company_id,
            'accounting_period_id' => $period?->id,
            'number' => NumberSeries::next($delivery->company_id, 'journal_entry'),
            'entry_date' => $delivery->delivery_date,
            'source_type' => 'delivery',
            'reference_type' => Delivery::class,
            'reference_id' => $delivery->id,
            'memo' => "Delivery {$delivery->number}",
            'total_debit' => $totalCost,
            'total_credit' => $totalCost,
            'status' => 'posted',
        ]);

        $journalEntry->lines()->createMany([
            ['chart_of_account_id' => $cogsAccount->id, 'description' => "DO {$delivery->number} — Cost of Goods Sold", 'debit' => $totalCost, 'credit' => 0],
            ['chart_of_account_id' => $inventoryAccount->id, 'description' => "DO {$delivery->number} — Inventory", 'debit' => 0, 'credit' => $totalCost],
        ]);

        return $journalEntry;
    }

    protected function refreshSalesOrderStatus(SalesOrder $salesOrder): void
    {
        $salesOrder->load('lines');

        $fullyDelivered = $salesOrder->lines->every(
            fn ($line) => bccomp((string) $line->delivered_quantity, (string) $line->quantity, 4) >= 0
        );

        $anyDelivered = $salesOrder->lines->contains(fn ($line) => (float) $line->delivered_quantity > 0);

        $salesOrder->forceFill([
            'status' => $fullyDelivered ? 'delivered' : ($anyDelivered ? 'partially_delivered' : $salesOrder->status),
        ])->save();
    }
}
