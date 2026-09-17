<?php

namespace Tests\Feature\Inventory;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\JournalEntry;
use App\Models\Inventory\StockOpname;
use App\Models\Master\Item;
use App\Models\Master\Uom;
use App\Models\Master\Warehouse;
use App\Services\Inventory\StockLedgerService;
use App\Services\Inventory\StockOpnamePostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnamePostingTest extends TestCase
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

        // Live ledger balance: 10 units @ 100.
        app(StockLedgerService::class)->receive($this->item->id, $this->warehouse->id, 10, 100, $this->company, '2026-01-05');
    }

    protected function makeOpname(float $physicalQuantity, float $formSystemQuantity = 0.0): StockOpname
    {
        $opname = StockOpname::create(['company_id' => $this->company->id, 'warehouse_id' => $this->warehouse->id, 'count_date' => '2026-01-10']);
        $opname->lines()->create(['item_id' => $this->item->id, 'system_quantity' => $formSystemQuantity, 'physical_quantity' => $physicalQuantity]);

        return $opname->fresh('lines');
    }

    public function test_counting_more_than_the_system_balance_books_a_gain_using_the_live_balance_not_the_stale_form_value(): void
    {
        // The form line was filled out with a stale system_quantity of 3 — posting must
        // reconcile against the real, current ledger balance (10), not this stale value.
        $opname = $this->makeOpname(physicalQuantity: 13, formSystemQuantity: 3);

        app(StockOpnamePostingService::class)->post($opname);

        $line = $opname->lines()->first()->fresh();
        $this->assertSame('10.0000', $line->system_quantity);
        $this->assertSame('3.0000', $line->variance_quantity);

        $balance = app(StockLedgerService::class)->currentBalance($this->item->id, $this->warehouse->id);
        $this->assertSame(13.0, $balance['quantity']);

        $journalEntry = JournalEntry::where('reference_type', StockOpname::class)->where('reference_id', $opname->id)->first();
        $this->assertNotNull($journalEntry);
        $this->assertSame('300.00', $journalEntry->total_debit); // 3 * 100 average cost
        $this->assertSame('posted', $opname->fresh()->status);
    }

    public function test_counting_less_than_the_system_balance_books_a_loss(): void
    {
        $opname = $this->makeOpname(physicalQuantity: 7);

        app(StockOpnamePostingService::class)->post($opname);

        $line = $opname->lines()->first()->fresh();
        $this->assertSame('-3.0000', $line->variance_quantity);

        $balance = app(StockLedgerService::class)->currentBalance($this->item->id, $this->warehouse->id);
        $this->assertSame(7.0, $balance['quantity']);

        $journalEntry = JournalEntry::where('reference_type', StockOpname::class)->where('reference_id', $opname->id)->first();
        $this->assertSame('300.00', $journalEntry->total_debit);
    }

    public function test_a_matching_count_creates_no_ledger_entry_or_journal(): void
    {
        $opname = $this->makeOpname(physicalQuantity: 10);

        app(StockOpnamePostingService::class)->post($opname);

        $line = $opname->lines()->first()->fresh();
        $this->assertSame('0.0000', $line->variance_quantity);

        $journalEntry = JournalEntry::where('reference_type', StockOpname::class)->where('reference_id', $opname->id)->first();
        $this->assertNull($journalEntry);

        $this->assertSame('posted', $opname->fresh()->status);
    }

    public function test_posting_twice_is_rejected(): void
    {
        $opname = $this->makeOpname(physicalQuantity: 10);

        app(StockOpnamePostingService::class)->post($opname);

        $this->expectException(\RuntimeException::class);
        app(StockOpnamePostingService::class)->post($opname->fresh());
    }
}
