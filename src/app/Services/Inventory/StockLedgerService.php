<?php

namespace App\Services\Inventory;

use App\Models\Inventory\StockLedgerEntry;
use App\Models\Master\Item;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Posts movements to the immutable stock ledger (`inventory_stock_ledger`,
 * PRD Key Rule #10 — append-only, no updated_at). `Item.average_cost` is a
 * single company-wide moving average (the schema has no per-warehouse cost
 * table): it only changes on receive() (new cost information entering the
 * company); issue() always costs out at the current average and leaves it
 * unchanged — standard moving-average costing. Per-(item, warehouse) ledger
 * rows still keep their own running balance_quantity/balance_value for
 * per-warehouse stock reporting.
 */
class StockLedgerService
{
    public function receive(
        int $itemId,
        int $warehouseId,
        float $quantity,
        float $unitCost,
        Model $source,
        DateTimeInterface|string $movementDate,
        ?string $batchNumber = null,
        string $movementType = 'goods_receipt',
        bool $affectsAverageCost = true,
    ): StockLedgerEntry {
        return DB::transaction(function () use ($itemId, $warehouseId, $quantity, $unitCost, $source, $movementDate, $batchNumber, $movementType, $affectsAverageCost) {
            $previous = $this->lockLastEntry($itemId, $warehouseId);

            $previousQty = (float) ($previous->balance_quantity ?? 0);
            $previousValue = (float) ($previous->balance_value ?? 0);

            $newQty = $previousQty + $quantity;
            $newValue = $previousValue + ($quantity * $unitCost);

            $entry = StockLedgerEntry::create([
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'movement_type' => $movementType,
                'source_type' => $source::class,
                'source_id' => $source->getKey(),
                'quantity_in' => $quantity,
                'quantity_out' => 0,
                'unit_cost' => $unitCost,
                'balance_quantity' => $newQty,
                'balance_value' => $newValue,
                'batch_number' => $batchNumber,
                'movement_date' => $movementDate,
                'created_at' => now(),
            ]);

            if ($affectsAverageCost) {
                $this->recalculateAverageCost($itemId, $quantity, $unitCost);
            }

            return $entry;
        });
    }

    /**
     * @throws RuntimeException if the warehouse doesn't hold enough of the item.
     */
    public function issue(
        int $itemId,
        int $warehouseId,
        float $quantity,
        Model $source,
        DateTimeInterface|string $movementDate,
        ?string $batchNumber = null,
        string $movementType = 'goods_issue',
    ): StockLedgerEntry {
        return DB::transaction(function () use ($itemId, $warehouseId, $quantity, $source, $movementDate, $batchNumber, $movementType) {
            $previous = $this->lockLastEntry($itemId, $warehouseId);

            $previousQty = (float) ($previous->balance_quantity ?? 0);
            $previousValue = (float) ($previous->balance_value ?? 0);

            if (bccomp((string) $quantity, (string) $previousQty, 4) > 0) {
                throw new RuntimeException("Insufficient stock: warehouse holds {$previousQty} but {$quantity} was requested.");
            }

            $item = Item::query()->whereKey($itemId)->lockForUpdate()->first();
            $unitCost = (float) ($item->average_cost ?? 0);

            $newQty = $previousQty - $quantity;
            $newValue = max(0.0, $previousValue - ($quantity * $unitCost));

            // Issues cost out at the current moving average and never change it.
            return StockLedgerEntry::create([
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'movement_type' => $movementType,
                'source_type' => $source::class,
                'source_id' => $source->getKey(),
                'quantity_in' => 0,
                'quantity_out' => $quantity,
                'unit_cost' => $unitCost,
                'balance_quantity' => $newQty,
                'balance_value' => $newValue,
                'batch_number' => $batchNumber,
                'movement_date' => $movementDate,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * @return array{0: StockLedgerEntry, 1: StockLedgerEntry} [outEntry, inEntry]
     */
    public function transfer(
        int $itemId,
        int $fromWarehouseId,
        int $toWarehouseId,
        float $quantity,
        Model $source,
        DateTimeInterface|string $movementDate,
        ?string $batchNumber = null,
    ): array {
        return DB::transaction(function () use ($itemId, $fromWarehouseId, $toWarehouseId, $quantity, $source, $movementDate, $batchNumber) {
            $outEntry = $this->issue($itemId, $fromWarehouseId, $quantity, $source, $movementDate, $batchNumber, 'transfer_out');

            // Carries the exact cost that left the source warehouse — a transfer
            // relocates value, it doesn't create or destroy it, so the company-wide
            // average cost is left untouched.
            $inEntry = $this->receive(
                $itemId, $toWarehouseId, $quantity, (float) $outEntry->unit_cost, $source,
                $movementDate, $batchNumber, 'transfer_in', affectsAverageCost: false,
            );

            return [$outEntry, $inEntry];
        });
    }

    /**
     * Posts a signed quantity correction (Stock Adjustment / Stock Opname
     * variance). Positive = found/increase (receive, optionally at a given
     * cost); negative = loss/decrease (issue, at current average cost).
     */
    public function adjust(
        int $itemId,
        int $warehouseId,
        float $signedQuantity,
        Model $source,
        DateTimeInterface|string $movementDate,
        float $unitCost = 0.0,
        string $movementType = 'adjustment',
    ): StockLedgerEntry {
        if ($signedQuantity === 0.0) {
            throw new InvalidArgumentException('Adjustment quantity cannot be zero.');
        }

        if ($signedQuantity > 0) {
            $item = Item::query()->whereKey($itemId)->first();
            $cost = $unitCost > 0 ? $unitCost : (float) ($item->average_cost ?? 0);

            return $this->receive($itemId, $warehouseId, $signedQuantity, $cost, $source, $movementDate, null, $movementType);
        }

        return $this->issue($itemId, $warehouseId, abs($signedQuantity), $source, $movementDate, null, $movementType);
    }

    public function currentBalance(int $itemId, int $warehouseId): array
    {
        $entry = StockLedgerEntry::query()
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('id')
            ->first();

        return [
            'quantity' => (float) ($entry->balance_quantity ?? 0),
            'value' => (float) ($entry->balance_value ?? 0),
        ];
    }

    protected function lockLastEntry(int $itemId, int $warehouseId): ?StockLedgerEntry
    {
        return StockLedgerEntry::query()
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();
    }

    protected function recalculateAverageCost(int $itemId, float $incomingQuantity, float $incomingUnitCost): void
    {
        $item = Item::query()->whereKey($itemId)->lockForUpdate()->first();

        if (! $item) {
            return;
        }

        $afterGlobalQty = $this->totalQuantityForItem($itemId);
        $beforeGlobalQty = $afterGlobalQty - $incomingQuantity;
        $beforeGlobalValue = $beforeGlobalQty * (float) $item->average_cost;
        $afterGlobalValue = $beforeGlobalValue + ($incomingQuantity * $incomingUnitCost);

        if ($afterGlobalQty > 0) {
            $item->update(['average_cost' => round($afterGlobalValue / $afterGlobalQty, 2)]);
        }
    }

    /**
     * Sums the latest balance_quantity per warehouse for an item — the
     * company-wide quantity on hand across every warehouse.
     */
    protected function totalQuantityForItem(int $itemId): float
    {
        $latestIds = StockLedgerEntry::query()
            ->where('item_id', $itemId)
            ->selectRaw('MAX(id) as id')
            ->groupBy('warehouse_id');

        return (float) StockLedgerEntry::query()
            ->joinSub($latestIds, 'latest', fn ($join) => $join->on('inventory_stock_ledger.id', '=', 'latest.id'))
            ->sum('balance_quantity');
    }
}
