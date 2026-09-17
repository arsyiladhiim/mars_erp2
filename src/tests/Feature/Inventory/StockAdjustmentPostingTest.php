<?php

namespace Tests\Feature\Inventory;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\GlAccountMapping;
use App\Models\Inventory\StockAdjustment;
use App\Models\Master\Item;
use App\Models\Master\Uom;
use App\Models\Master\Warehouse;
use App\Services\Inventory\StockAdjustmentPostingService;
use App\Services\Inventory\StockLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class StockAdjustmentPostingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Warehouse $warehouse;

    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $fy = FiscalYear::create(['company_id' => $this->company->id, 'code' => 'FY2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open']);
        AccountingPeriod::create(['fiscal_year_id' => $fy->id, 'name' => 'January 2026', 'period_number' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'open']);

        $uom = Uom::create(['code' => 'PCS', 'name' => 'Pieces']);
        $this->warehouse = Warehouse::create(['company_id' => $this->company->id, 'code' => 'WH1', 'name' => 'Main Warehouse']);
        $this->item = Item::create(['sku' => 'ITEM1', 'name' => 'Widget', 'uom_id' => $uom->id]);

        $inventoryAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1301', 'name' => 'Inventory', 'account_type' => 'asset']);
        $adjustmentAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '6103', 'name' => 'Inventory Adjustment Gain/Loss', 'account_type' => 'expense']);

        GlAccountMapping::ensureSeeded($this->company->id);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'inventory')->update(['chart_of_account_id' => $inventoryAccount->id]);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'inventory_adjustment')->update(['chart_of_account_id' => $adjustmentAccount->id]);

        // Seed initial stock of 10 units @ 100 so decreases have something to draw from.
        app(StockLedgerService::class)->receive($this->item->id, $this->warehouse->id, 10, 100, $this->company, '2026-01-05');
    }

    protected function makeAdjustment(float $quantity, float $unitCost = 0.0): StockAdjustment
    {
        $adjustment = StockAdjustment::create([
            'company_id' => $this->company->id, 'warehouse_id' => $this->warehouse->id, 'adjustment_date' => '2026-01-10',
        ]);
        $adjustment->lines()->create(['item_id' => $this->item->id, 'quantity' => $quantity, 'unit_cost' => $unitCost]);

        return $adjustment->fresh('lines');
    }

    public function test_a_positive_adjustment_increases_stock_and_books_a_gain(): void
    {
        $adjustment = $this->makeAdjustment(5, 120);

        app(StockAdjustmentPostingService::class)->post($adjustment);

        $balance = app(StockLedgerService::class)->currentBalance($this->item->id, $this->warehouse->id);
        $this->assertSame(15.0, $balance['quantity']);
        $this->assertSame('posted', $adjustment->fresh()->status);

        $journalEntry = \App\Models\Finance\JournalEntry::where('reference_type', StockAdjustment::class)->where('reference_id', $adjustment->id)->first();
        $this->assertNotNull($journalEntry);
        $this->assertTrue($journalEntry->isBalanced());
        $this->assertSame('600.00', $journalEntry->total_debit); // 5 * 120

        $debitLine = $journalEntry->lines()->where('debit', '>', 0)->first();
        $this->assertSame('Inventory', $debitLine->chartOfAccount->name);
    }

    public function test_a_negative_adjustment_decreases_stock_and_books_a_loss(): void
    {
        $adjustment = $this->makeAdjustment(-4);

        app(StockAdjustmentPostingService::class)->post($adjustment);

        $balance = app(StockLedgerService::class)->currentBalance($this->item->id, $this->warehouse->id);
        $this->assertSame(6.0, $balance['quantity']);

        $journalEntry = \App\Models\Finance\JournalEntry::where('reference_type', StockAdjustment::class)->where('reference_id', $adjustment->id)->first();
        $this->assertSame('400.00', $journalEntry->total_debit); // 4 * 100 (current average cost)

        $creditLine = $journalEntry->lines()->where('credit', '>', 0)->first();
        $this->assertSame('Inventory', $creditLine->chartOfAccount->name);
    }

    public function test_decreasing_more_than_available_is_blocked(): void
    {
        $adjustment = $this->makeAdjustment(-50);

        $this->expectException(RuntimeException::class);
        app(StockAdjustmentPostingService::class)->post($adjustment);
    }

    public function test_posting_twice_is_rejected(): void
    {
        $adjustment = $this->makeAdjustment(2);

        app(StockAdjustmentPostingService::class)->post($adjustment);

        $this->expectException(RuntimeException::class);
        app(StockAdjustmentPostingService::class)->post($adjustment->fresh());
    }
}
