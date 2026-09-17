<?php

namespace Tests\Feature\Procurement;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\SupplierInvoice;
use App\Models\Inventory\GoodsReceipt;
use App\Models\Master\BusinessPartner;
use App\Models\Master\Item;
use App\Models\Master\Uom;
use App\Models\Master\Warehouse;
use App\Services\Procurement\SupplierInvoicePostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SupplierInvoicePostingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected GoodsReceipt $goodsReceipt;

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
        $warehouse = Warehouse::create(['company_id' => $this->company->id, 'code' => 'WH1', 'name' => 'Main Warehouse']);
        $supplier = BusinessPartner::create(['code' => 'SUP1', 'name' => 'Supplier One', 'type' => 'supplier']);
        $item = Item::create(['sku' => 'ITEM1', 'name' => 'Widget', 'uom_id' => $uom->id]);

        $grIrAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1401', 'name' => 'GR/IR Clearing', 'account_type' => 'liability']);
        $apAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '2101', 'name' => 'Accounts Payable', 'account_type' => 'liability']);
        $taxInputAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '2202', 'name' => 'VAT Receivable', 'account_type' => 'asset']);

        GlAccountMapping::ensureSeeded($this->company->id);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'gr_ir_clearing')->update(['chart_of_account_id' => $grIrAccount->id]);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'accounts_payable')->update(['chart_of_account_id' => $apAccount->id]);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'tax_input')->update(['chart_of_account_id' => $taxInputAccount->id]);

        $this->goodsReceipt = GoodsReceipt::create([
            'company_id' => $this->company->id, 'business_partner_id' => $supplier->id,
            'warehouse_id' => $warehouse->id, 'receipt_date' => '2026-01-10', 'status' => 'posted',
        ]);
        $this->goodsReceipt->lines()->create(['item_id' => $item->id, 'quantity' => 10, 'unit_cost' => 100]);
    }

    protected function makeInvoice(array $overrides = []): SupplierInvoice
    {
        return SupplierInvoice::create(array_merge([
            'company_id' => $this->company->id,
            'business_partner_id' => $this->goodsReceipt->business_partner_id,
            'goods_receipt_id' => $this->goodsReceipt->id,
            'invoice_date' => '2026-01-12',
            'subtotal' => 1000, 'tax_total' => 110, 'grand_total' => 1110,
        ], $overrides));
    }

    public function test_posting_creates_a_balanced_ap_journal_entry(): void
    {
        $invoice = $this->makeInvoice();

        $journalEntry = app(SupplierInvoicePostingService::class)->post($invoice->fresh());

        $this->assertTrue($journalEntry->isBalanced());
        $this->assertSame('1110.00', $journalEntry->total_debit);
        $this->assertSame(3, $journalEntry->lines()->count());
        $this->assertSame('posted', $invoice->fresh()->status);

        $apLine = $journalEntry->lines()->where('credit', '>', 0)->first();
        $this->assertSame('1110.00', $apLine->credit);
    }

    public function test_posting_without_a_linked_goods_receipt_is_rejected(): void
    {
        $invoice = $this->makeInvoice(['goods_receipt_id' => null]);

        $this->expectException(RuntimeException::class);
        app(SupplierInvoicePostingService::class)->post($invoice->fresh());
    }

    public function test_posting_with_mismatched_totals_is_rejected(): void
    {
        $invoice = $this->makeInvoice(['grand_total' => 9999]);

        $this->expectException(RuntimeException::class);
        app(SupplierInvoicePostingService::class)->post($invoice->fresh());
    }

    public function test_posting_twice_is_rejected(): void
    {
        $invoice = $this->makeInvoice();

        app(SupplierInvoicePostingService::class)->post($invoice->fresh());

        $this->expectException(RuntimeException::class);
        app(SupplierInvoicePostingService::class)->post($invoice->fresh());
    }
}
