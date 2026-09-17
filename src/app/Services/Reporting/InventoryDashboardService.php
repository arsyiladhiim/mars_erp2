<?php

namespace App\Services\Reporting;

use App\Models\Master\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Inventory Dashboard (PRD §27/§56): stock value, low-stock items, and
 * recent movement — all read from the existing immutable stock ledger, no
 * new tables. `master_items` isn't company-scoped in this schema, so
 * quantities are scoped by joining through the (company-scoped) warehouse.
 */
class InventoryDashboardService
{
    public function totalStockValue(int $companyId): float
    {
        return (float) $this->latestBalances($companyId)->sum('balance_value');
    }

    public function lowStockItems(int $companyId): Collection
    {
        $onHandByItem = $this->latestBalances($companyId)
            ->groupBy('item_id')
            ->select('item_id', DB::raw('SUM(balance_quantity) as on_hand'))
            ->pluck('on_hand', 'item_id');

        return Item::query()
            ->where('reorder_point', '>', 0)
            ->get()
            ->map(fn (Item $item) => [
                'sku' => $item->sku,
                'name' => $item->name,
                'on_hand' => (float) ($onHandByItem[$item->id] ?? 0),
                'reorder_point' => (float) $item->reorder_point,
            ])
            ->filter(fn (array $row) => $row['on_hand'] <= $row['reorder_point'])
            ->sortBy('on_hand')
            ->values();
    }

    public function recentMovements(int $companyId, int $limit = 20): Collection
    {
        return DB::table('inventory_stock_ledger as l')
            ->join('master_items as i', 'i.id', '=', 'l.item_id')
            ->join('master_warehouses as w', 'w.id', '=', 'l.warehouse_id')
            ->where('w.company_id', $companyId)
            ->orderByDesc('l.id')
            ->limit($limit)
            ->select(['i.sku', 'i.name as item_name', 'w.name as warehouse_name', 'l.movement_type', 'l.quantity_in', 'l.quantity_out', 'l.movement_date'])
            ->get();
    }

    /**
     * Query builder over the latest StockLedgerEntry row per (item_id,
     * warehouse_id), scoped to a company via its warehouses. Callers add
     * their own select()/groupBy()/aggregate on top.
     */
    protected function latestBalances(int $companyId)
    {
        $latestIds = DB::table('inventory_stock_ledger as l')
            ->join('master_warehouses as w', 'w.id', '=', 'l.warehouse_id')
            ->where('w.company_id', $companyId)
            ->selectRaw('MAX(l.id) as id')
            ->groupBy('l.item_id', 'l.warehouse_id');

        return DB::table('inventory_stock_ledger')
            ->joinSub($latestIds, 'ids', fn ($join) => $join->on('inventory_stock_ledger.id', '=', 'ids.id'));
    }
}
