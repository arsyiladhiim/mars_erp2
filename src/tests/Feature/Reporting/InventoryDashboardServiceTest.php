<?php

namespace Tests\Feature\Reporting;

use App\Models\Core\Company;
use App\Models\Master\Item;
use App\Models\Master\Uom;
use App\Models\Master\Warehouse;
use App\Services\Inventory\StockLedgerService;
use App\Services\Reporting\InventoryDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_stock_value_sums_across_items_and_warehouses(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $uom = Uom::create(['code' => 'PCS', 'name' => 'Pieces']);
        $whA = Warehouse::create(['company_id' => $company->id, 'code' => 'WHA', 'name' => 'Warehouse A']);
        $whB = Warehouse::create(['company_id' => $company->id, 'code' => 'WHB', 'name' => 'Warehouse B']);
        $itemA = Item::create(['sku' => 'ITEM-A', 'name' => 'Widget', 'uom_id' => $uom->id]);
        $itemB = Item::create(['sku' => 'ITEM-B', 'name' => 'Gadget', 'uom_id' => $uom->id]);

        app(StockLedgerService::class)->receive($itemA->id, $whA->id, 10, 100, $company, '2026-01-05');
        app(StockLedgerService::class)->receive($itemB->id, $whB->id, 5, 200, $company, '2026-01-05');

        $total = app(InventoryDashboardService::class)->totalStockValue($company->id);

        $this->assertSame(2000.0, $total); // 10*100 + 5*200
    }

    public function test_low_stock_items_are_flagged_and_sorted_by_scarcity(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $uom = Uom::create(['code' => 'PCS', 'name' => 'Pieces']);
        $warehouse = Warehouse::create(['company_id' => $company->id, 'code' => 'WH1', 'name' => 'Main Warehouse']);

        $low = Item::create(['sku' => 'LOW', 'name' => 'Low Stock Item', 'uom_id' => $uom->id, 'reorder_point' => 20]);
        $ok = Item::create(['sku' => 'OK', 'name' => 'Well Stocked Item', 'uom_id' => $uom->id, 'reorder_point' => 5]);

        app(StockLedgerService::class)->receive($low->id, $warehouse->id, 5, 50, $company, '2026-01-05');
        app(StockLedgerService::class)->receive($ok->id, $warehouse->id, 100, 50, $company, '2026-01-05');

        $lowStock = app(InventoryDashboardService::class)->lowStockItems($company->id);

        $this->assertCount(1, $lowStock);
        $this->assertSame('LOW', $lowStock->first()['sku']);
    }
}
