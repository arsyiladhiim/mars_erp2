<?php

namespace App\Services\Inventory;

use App\Models\Audit\AuditLog;
use App\Models\Core\AccountingPeriod;
use App\Models\Inventory\StockTransfer;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Posts a draft Stock Transfer: an issue()/receive() pair per line moving
 * quantity (and its exact carried cost) from one warehouse to another. No
 * G/L impact — it's the same company-wide inventory asset, just relocated.
 */
class StockTransferPostingService
{
    public function __construct(protected StockLedgerService $stockLedger) {}

    public function post(StockTransfer $transfer): void
    {
        if ($transfer->status !== 'draft') {
            throw new RuntimeException("Stock Transfer {$transfer->number} has already been posted.");
        }

        $transfer->loadMissing('lines');

        if ($transfer->lines->isEmpty()) {
            throw new RuntimeException('Cannot post a Stock Transfer with no lines.');
        }

        if ($transfer->from_warehouse_id === $transfer->to_warehouse_id) {
            throw new RuntimeException('Source and destination warehouse must be different.');
        }

        AccountingPeriod::assertOpenForPosting($transfer->transfer_date, $transfer->company_id);

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->lines as $line) {
                $this->stockLedger->transfer(
                    itemId: $line->item_id,
                    fromWarehouseId: $transfer->from_warehouse_id,
                    toWarehouseId: $transfer->to_warehouse_id,
                    quantity: (float) $line->quantity,
                    source: $line,
                    movementDate: $transfer->transfer_date,
                    batchNumber: $line->batch_number,
                );
            }

            $transfer->forceFill(['status' => 'completed'])->save();

            AuditLog::record('posted', StockTransfer::class, $transfer->getKey());
        });
    }
}
