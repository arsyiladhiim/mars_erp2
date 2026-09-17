<?php

namespace Tests\Feature\Inventory;

use App\Models\Core\Company;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockOpname;
use App\Models\Inventory\StockTransfer;
use App\Models\Master\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_transfer_is_assigned_an_sto_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $whA = Warehouse::create(['company_id' => $company->id, 'code' => 'WHA', 'name' => 'Warehouse A']);
        $whB = Warehouse::create(['company_id' => $company->id, 'code' => 'WHB', 'name' => 'Warehouse B']);

        $transfer = StockTransfer::create([
            'company_id' => $company->id, 'from_warehouse_id' => $whA->id, 'to_warehouse_id' => $whB->id, 'transfer_date' => '2026-01-10',
        ]);

        $this->assertStringStartsWith('STO-', $transfer->number);
    }

    public function test_stock_adjustment_is_assigned_an_adj_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $warehouse = Warehouse::create(['company_id' => $company->id, 'code' => 'WH1', 'name' => 'Main Warehouse']);

        $adjustment = StockAdjustment::create(['company_id' => $company->id, 'warehouse_id' => $warehouse->id, 'adjustment_date' => '2026-01-10']);

        $this->assertStringStartsWith('ADJ-', $adjustment->number);
    }

    public function test_stock_opname_is_assigned_an_opn_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $warehouse = Warehouse::create(['company_id' => $company->id, 'code' => 'WH1', 'name' => 'Main Warehouse']);

        $opname = StockOpname::create(['company_id' => $company->id, 'warehouse_id' => $warehouse->id, 'count_date' => '2026-01-10']);

        $this->assertStringStartsWith('OPN-', $opname->number);
    }
}
