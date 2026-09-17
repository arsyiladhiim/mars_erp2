<?php

namespace Tests\Feature\Inventory;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Inventory\StockLedgerEntry;
use App\Models\Inventory\StockTransfer;
use App\Models\Master\Item;
use App\Models\Master\Uom;
use App\Models\Master\Warehouse;
use App\Services\Inventory\StockLedgerService;
use App\Services\Inventory\StockTransferPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class StockTransferPostingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Warehouse $warehouseA;

    protected Warehouse $warehouseB;

    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $fy = FiscalYear::create(['company_id' => $this->company->id, 'code' => 'FY2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open']);
        AccountingPeriod::create(['fiscal_year_id' => $fy->id, 'name' => 'January 2026', 'period_number' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'open']);

        $uom = Uom::create(['code' => 'PCS', 'name' => 'Pieces']);
        $this->warehouseA = Warehouse::create(['company_id' => $this->company->id, 'code' => 'WHA', 'name' => 'Warehouse A']);
        $this->warehouseB = Warehouse::create(['company_id' => $this->company->id, 'code' => 'WHB', 'name' => 'Warehouse B']);
        $this->item = Item::create(['sku' => 'ITEM1', 'name' => 'Widget', 'uom_id' => $uom->id]);

        // Seed initial stock of 20 units @ 100 into Warehouse A.
        app(StockLedgerService::class)->receive(
            $this->item->id, $this->warehouseA->id, 20, 100, $this->company, '2026-01-05',
        );
    }

    protected function makeTransfer(float $quantity): StockTransfer
    {
        $transfer = StockTransfer::create([
            'company_id' => $this->company->id, 'from_warehouse_id' => $this->warehouseA->id,
            'to_warehouse_id' => $this->warehouseB->id, 'transfer_date' => '2026-01-10',
        ]);
        $transfer->lines()->create(['item_id' => $this->item->id, 'quantity' => $quantity]);

        return $transfer->fresh('lines');
    }

    public function test_posting_moves_quantity_and_carries_the_exact_cost_without_changing_the_average(): void
    {
        $transfer = $this->makeTransfer(8);

        app(StockTransferPostingService::class)->post($transfer);

        $sourceBalance = app(StockLedgerService::class)->currentBalance($this->item->id, $this->warehouseA->id);
        $destBalance = app(StockLedgerService::class)->currentBalance($this->item->id, $this->warehouseB->id);

        $this->assertSame(12.0, $sourceBalance['quantity']);
        $this->assertSame(8.0, $destBalance['quantity']);
        $this->assertSame(800.0, $destBalance['value']);

        // Transfers relocate value, they don't create/destroy it — average cost is untouched.
        $this->assertSame('100.00', $this->item->fresh()->average_cost);

        $this->assertSame('completed', $transfer->fresh()->status);

        $inEntry = StockLedgerEntry::where('warehouse_id', $this->warehouseB->id)->first();
        $this->assertSame('transfer_in', $inEntry->movement_type);
        $this->assertSame('100.00', $inEntry->unit_cost);
    }

    public function test_transferring_more_than_available_is_blocked(): void
    {
        $transfer = $this->makeTransfer(100);

        $this->expectException(RuntimeException::class);
        app(StockTransferPostingService::class)->post($transfer);
    }

    public function test_transferring_to_the_same_warehouse_is_blocked(): void
    {
        $transfer = StockTransfer::create([
            'company_id' => $this->company->id, 'from_warehouse_id' => $this->warehouseA->id,
            'to_warehouse_id' => $this->warehouseA->id, 'transfer_date' => '2026-01-10',
        ]);
        $transfer->lines()->create(['item_id' => $this->item->id, 'quantity' => 5]);

        $this->expectException(RuntimeException::class);
        app(StockTransferPostingService::class)->post($transfer->fresh('lines'));
    }

    public function test_posting_twice_is_rejected(): void
    {
        $transfer = $this->makeTransfer(5);

        app(StockTransferPostingService::class)->post($transfer);

        $this->expectException(RuntimeException::class);
        app(StockTransferPostingService::class)->post($transfer->fresh());
    }
}
