<?php

namespace Tests\Feature\Procurement;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\GlAccountMapping;
use App\Models\Inventory\GoodsReceipt;
use App\Models\Inventory\StockLedgerEntry;
use App\Models\Master\BusinessPartner;
use App\Models\Master\Item;
use App\Models\Master\Uom;
use App\Models\Master\Warehouse;
use App\Models\Procurement\PurchaseOrder;
use App\Services\Procurement\GoodsReceiptPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class GoodsReceiptPostingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Warehouse $warehouse;

    protected Item $item;

    protected BusinessPartner $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $fy = FiscalYear::create([
            'company_id' => $this->company->id, 'code' => 'FY2026',
            'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open',
        ]);
        AccountingPeriod::create([
            'fiscal_year_id' => $fy->id, 'name' => 'January 2026', 'period_number' => 1,
            'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'open',
        ]);

        $uom = Uom::create(['code' => 'PCS', 'name' => 'Pieces']);
        $this->warehouse = Warehouse::create(['company_id' => $this->company->id, 'code' => 'WH1', 'name' => 'Main Warehouse']);
        $this->supplier = BusinessPartner::create(['code' => 'SUP1', 'name' => 'Supplier One', 'type' => 'supplier']);
        $this->item = Item::create(['sku' => 'ITEM1', 'name' => 'Widget', 'uom_id' => $uom->id]);

        $inventoryAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1301', 'name' => 'Inventory', 'account_type' => 'asset']);
        $grIrAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1401', 'name' => 'GR/IR Clearing', 'account_type' => 'liability']);

        GlAccountMapping::ensureSeeded($this->company->id);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'inventory')->update(['chart_of_account_id' => $inventoryAccount->id]);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'gr_ir_clearing')->update(['chart_of_account_id' => $grIrAccount->id]);
    }

    protected function makeApprovedPurchaseOrder(float $quantity, float $unitPrice): PurchaseOrder
    {
        $po = PurchaseOrder::create([
            'company_id' => $this->company->id, 'business_partner_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id, 'order_date' => '2026-01-05',
            'grand_total' => $quantity * $unitPrice, 'status' => 'approved',
        ]);

        $po->lines()->create([
            'item_id' => $this->item->id, 'quantity' => $quantity, 'unit_price' => $unitPrice,
            'line_total' => $quantity * $unitPrice,
        ]);

        return $po->fresh('lines');
    }

    public function test_posting_a_full_receipt_creates_stock_ledger_entry_and_balanced_journal(): void
    {
        $po = $this->makeApprovedPurchaseOrder(10, 100);
        $poLine = $po->lines->first();

        $gr = GoodsReceipt::create([
            'company_id' => $this->company->id, 'purchase_order_id' => $po->id,
            'business_partner_id' => $this->supplier->id, 'warehouse_id' => $this->warehouse->id,
            'receipt_date' => '2026-01-10',
        ]);
        $gr->lines()->create([
            'purchase_order_line_id' => $poLine->id, 'item_id' => $this->item->id,
            'quantity' => 10, 'unit_cost' => 100,
        ]);

        $journalEntry = app(GoodsReceiptPostingService::class)->post($gr->fresh());

        $ledgerEntry = StockLedgerEntry::where('item_id', $this->item->id)->where('warehouse_id', $this->warehouse->id)->first();
        $this->assertNotNull($ledgerEntry);
        $this->assertSame('10.0000', $ledgerEntry->quantity_in);
        $this->assertSame('1000.00', $ledgerEntry->balance_value);
        $this->assertSame('10.0000', $ledgerEntry->balance_quantity);

        $this->assertSame('100.00', $this->item->fresh()->average_cost);

        $this->assertTrue($journalEntry->isBalanced());
        $this->assertSame('1000.00', $journalEntry->total_debit);
        $this->assertSame(2, $journalEntry->lines()->count());

        $this->assertSame('10.0000', $poLine->fresh()->received_quantity);
        $this->assertSame('received', $po->fresh()->status);
        $this->assertSame('posted', $gr->fresh()->status);
    }

    public function test_partial_receipt_leaves_purchase_order_partially_received(): void
    {
        $po = $this->makeApprovedPurchaseOrder(10, 100);
        $poLine = $po->lines->first();

        $gr = GoodsReceipt::create([
            'company_id' => $this->company->id, 'purchase_order_id' => $po->id,
            'business_partner_id' => $this->supplier->id, 'warehouse_id' => $this->warehouse->id,
            'receipt_date' => '2026-01-10',
        ]);
        $gr->lines()->create([
            'purchase_order_line_id' => $poLine->id, 'item_id' => $this->item->id,
            'quantity' => 4, 'unit_cost' => 100,
        ]);

        app(GoodsReceiptPostingService::class)->post($gr->fresh());

        $this->assertSame('partially_received', $po->fresh()->status);
    }

    public function test_second_receipt_at_a_different_cost_updates_moving_average(): void
    {
        $po = $this->makeApprovedPurchaseOrder(10, 100);
        $poLine = $po->lines->first();

        $gr1 = GoodsReceipt::create([
            'company_id' => $this->company->id, 'purchase_order_id' => $po->id,
            'business_partner_id' => $this->supplier->id, 'warehouse_id' => $this->warehouse->id,
            'receipt_date' => '2026-01-10',
        ]);
        $gr1->lines()->create(['purchase_order_line_id' => $poLine->id, 'item_id' => $this->item->id, 'quantity' => 10, 'unit_cost' => 100]);
        app(GoodsReceiptPostingService::class)->post($gr1->fresh());

        $gr2 = GoodsReceipt::create([
            'company_id' => $this->company->id, 'business_partner_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id, 'receipt_date' => '2026-01-15',
        ]);
        $gr2->lines()->create(['item_id' => $this->item->id, 'quantity' => 10, 'unit_cost' => 200]);
        app(GoodsReceiptPostingService::class)->post($gr2->fresh());

        // (10*100 + 10*200) / 20 = 150.00
        $this->assertSame('150.00', $this->item->fresh()->average_cost);

        $latestLedgerEntry = StockLedgerEntry::where('item_id', $this->item->id)->orderByDesc('id')->first();
        $this->assertSame('20.0000', $latestLedgerEntry->balance_quantity);
        $this->assertSame('3000.00', $latestLedgerEntry->balance_value);
    }

    public function test_receiving_against_an_unapproved_purchase_order_is_blocked(): void
    {
        $po = $this->makeApprovedPurchaseOrder(10, 100);
        $po->forceFill(['status' => 'draft'])->save();
        $poLine = $po->lines->first();

        $gr = GoodsReceipt::create([
            'company_id' => $this->company->id, 'purchase_order_id' => $po->id,
            'business_partner_id' => $this->supplier->id, 'warehouse_id' => $this->warehouse->id,
            'receipt_date' => '2026-01-10',
        ]);
        $gr->lines()->create(['purchase_order_line_id' => $poLine->id, 'item_id' => $this->item->id, 'quantity' => 10, 'unit_cost' => 100]);

        $this->expectException(RuntimeException::class);
        app(GoodsReceiptPostingService::class)->post($gr->fresh());
    }

    public function test_posting_twice_is_rejected(): void
    {
        $po = $this->makeApprovedPurchaseOrder(10, 100);
        $poLine = $po->lines->first();

        $gr = GoodsReceipt::create([
            'company_id' => $this->company->id, 'purchase_order_id' => $po->id,
            'business_partner_id' => $this->supplier->id, 'warehouse_id' => $this->warehouse->id,
            'receipt_date' => '2026-01-10',
        ]);
        $gr->lines()->create(['purchase_order_line_id' => $poLine->id, 'item_id' => $this->item->id, 'quantity' => 10, 'unit_cost' => 100]);

        app(GoodsReceiptPostingService::class)->post($gr->fresh());

        $this->expectException(RuntimeException::class);
        app(GoodsReceiptPostingService::class)->post($gr->fresh());
    }

    public function test_posting_is_blocked_when_the_accounting_period_is_closed(): void
    {
        AccountingPeriod::query()->update(['status' => 'closed']);

        $po = $this->makeApprovedPurchaseOrder(10, 100);
        $poLine = $po->lines->first();

        $gr = GoodsReceipt::create([
            'company_id' => $this->company->id, 'purchase_order_id' => $po->id,
            'business_partner_id' => $this->supplier->id, 'warehouse_id' => $this->warehouse->id,
            'receipt_date' => '2026-01-10',
        ]);
        $gr->lines()->create(['purchase_order_line_id' => $poLine->id, 'item_id' => $this->item->id, 'quantity' => 10, 'unit_cost' => 100]);

        $this->expectException(RuntimeException::class);
        app(GoodsReceiptPostingService::class)->post($gr->fresh());
    }
}
