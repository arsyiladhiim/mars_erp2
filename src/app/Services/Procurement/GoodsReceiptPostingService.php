<?php

namespace App\Services\Procurement;

use App\Models\Audit\AuditLog;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\NumberSeries;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\JournalEntry;
use App\Models\Inventory\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Services\Inventory\StockLedgerService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Posts a draft Goods Receipt: stock-in per line via StockLedgerService,
 * a balanced GR/IR clearing journal entry (Dr Inventory / Cr GR/IR), and
 * rolls the received quantity + status back up to the source Purchase Order.
 */
class GoodsReceiptPostingService
{
    public function __construct(protected StockLedgerService $stockLedger) {}

    public function post(GoodsReceipt $goodsReceipt): JournalEntry
    {
        if ($goodsReceipt->status !== 'draft') {
            throw new RuntimeException("Goods Receipt {$goodsReceipt->number} has already been posted.");
        }

        $goodsReceipt->loadMissing('lines.purchaseOrderLine', 'purchaseOrder.lines');

        if ($goodsReceipt->lines->isEmpty()) {
            throw new RuntimeException('Cannot post a Goods Receipt with no lines.');
        }

        if ($goodsReceipt->purchaseOrder && in_array($goodsReceipt->purchaseOrder->status, ['draft', 'pending_approval', 'rejected', 'cancelled'], true)) {
            throw new RuntimeException("Purchase Order {$goodsReceipt->purchaseOrder->number} must be approved before goods can be received against it.");
        }

        AccountingPeriod::assertOpenForPosting($goodsReceipt->receipt_date, $goodsReceipt->company_id);

        return DB::transaction(function () use ($goodsReceipt) {
            $totalValue = 0.0;

            foreach ($goodsReceipt->lines as $line) {
                $this->stockLedger->receive(
                    itemId: $line->item_id,
                    warehouseId: $goodsReceipt->warehouse_id,
                    quantity: (float) $line->quantity,
                    unitCost: (float) $line->unit_cost,
                    source: $line,
                    movementDate: $goodsReceipt->receipt_date,
                    batchNumber: $line->batch_number,
                );

                $totalValue += (float) $line->quantity * (float) $line->unit_cost;

                if ($line->purchaseOrderLine) {
                    $line->purchaseOrderLine->increment('received_quantity', $line->quantity);
                }
            }

            $journalEntry = $this->postJournalEntry($goodsReceipt, round($totalValue, 2));

            if ($goodsReceipt->purchaseOrder) {
                $this->refreshPurchaseOrderStatus($goodsReceipt->purchaseOrder);
            }

            $goodsReceipt->forceFill(['status' => 'posted'])->save();

            AuditLog::record('posted', GoodsReceipt::class, $goodsReceipt->getKey(), null, [
                'journal_entry_id' => $journalEntry->id,
                'total_value' => $totalValue,
            ]);

            return $journalEntry;
        });
    }

    protected function postJournalEntry(GoodsReceipt $goodsReceipt, float $totalValue): JournalEntry
    {
        $inventoryAccount = GlAccountMapping::resolve('inventory', $goodsReceipt->company_id);
        $grIrAccount = GlAccountMapping::resolve('gr_ir_clearing', $goodsReceipt->company_id);
        $period = AccountingPeriod::forDate($goodsReceipt->receipt_date, $goodsReceipt->company_id);

        $journalEntry = JournalEntry::create([
            'company_id' => $goodsReceipt->company_id,
            'accounting_period_id' => $period?->id,
            'number' => NumberSeries::next($goodsReceipt->company_id, 'journal_entry'),
            'entry_date' => $goodsReceipt->receipt_date,
            'source_type' => 'goods_receipt',
            'reference_type' => GoodsReceipt::class,
            'reference_id' => $goodsReceipt->id,
            'memo' => "Goods Receipt {$goodsReceipt->number}",
            'total_debit' => $totalValue,
            'total_credit' => $totalValue,
            'status' => 'posted',
        ]);

        $journalEntry->lines()->createMany([
            ['chart_of_account_id' => $inventoryAccount->id, 'description' => "GR {$goodsReceipt->number} — Inventory", 'debit' => $totalValue, 'credit' => 0],
            ['chart_of_account_id' => $grIrAccount->id, 'description' => "GR {$goodsReceipt->number} — GR/IR Clearing", 'debit' => 0, 'credit' => $totalValue],
        ]);

        return $journalEntry;
    }

    protected function refreshPurchaseOrderStatus(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrder->load('lines');

        $fullyReceived = $purchaseOrder->lines->every(
            fn ($line) => bccomp((string) $line->received_quantity, (string) $line->quantity, 4) >= 0
        );

        $anyReceived = $purchaseOrder->lines->contains(fn ($line) => (float) $line->received_quantity > 0);

        $purchaseOrder->forceFill([
            'status' => $fullyReceived ? 'received' : ($anyReceived ? 'partially_received' : $purchaseOrder->status),
        ])->save();
    }
}
