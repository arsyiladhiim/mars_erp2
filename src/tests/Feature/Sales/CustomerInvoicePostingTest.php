<?php

namespace Tests\Feature\Sales;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\GlAccountMapping;
use App\Models\Master\BusinessPartner;
use App\Models\Sales\CustomerInvoice;
use App\Services\Sales\CustomerInvoicePostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CustomerInvoicePostingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected BusinessPartner $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $fy = FiscalYear::create(['company_id' => $this->company->id, 'code' => 'FY2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open']);
        AccountingPeriod::create(['fiscal_year_id' => $fy->id, 'name' => 'January 2026', 'period_number' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'open']);

        $this->customer = BusinessPartner::create(['code' => 'CUST1', 'name' => 'Customer One', 'type' => 'customer']);

        $arAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1201', 'name' => 'Accounts Receivable', 'account_type' => 'asset']);
        $revenueAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '4101', 'name' => 'Sales Revenue', 'account_type' => 'revenue']);
        $taxOutputAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '2201', 'name' => 'VAT Payable', 'account_type' => 'liability']);

        GlAccountMapping::ensureSeeded($this->company->id);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'accounts_receivable')->update(['chart_of_account_id' => $arAccount->id]);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'sales_revenue')->update(['chart_of_account_id' => $revenueAccount->id]);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'tax_output')->update(['chart_of_account_id' => $taxOutputAccount->id]);
    }

    protected function makeInvoice(array $overrides = []): CustomerInvoice
    {
        return CustomerInvoice::create(array_merge([
            'company_id' => $this->company->id, 'business_partner_id' => $this->customer->id,
            'invoice_date' => '2026-01-12', 'subtotal' => 1000, 'tax_total' => 110, 'grand_total' => 1110,
        ], $overrides));
    }

    public function test_posting_creates_a_balanced_ar_journal_entry_with_tax(): void
    {
        $invoice = $this->makeInvoice();

        $journalEntry = app(CustomerInvoicePostingService::class)->post($invoice->fresh());

        $this->assertTrue($journalEntry->isBalanced());
        $this->assertSame('1110.00', $journalEntry->total_debit);
        $this->assertSame(3, $journalEntry->lines()->count());
        $this->assertSame('posted', $invoice->fresh()->status);

        $debitLine = $journalEntry->lines()->where('debit', '>', 0)->first();
        $this->assertSame('1110.00', $debitLine->debit);
    }

    public function test_posting_with_no_tax_creates_only_two_lines(): void
    {
        $invoice = $this->makeInvoice(['tax_total' => 0, 'grand_total' => 1000]);

        $journalEntry = app(CustomerInvoicePostingService::class)->post($invoice->fresh());

        $this->assertSame(2, $journalEntry->lines()->count());
    }

    public function test_posting_with_mismatched_totals_is_rejected(): void
    {
        $invoice = $this->makeInvoice(['grand_total' => 9999]);

        $this->expectException(RuntimeException::class);
        app(CustomerInvoicePostingService::class)->post($invoice->fresh());
    }

    public function test_posting_twice_is_rejected(): void
    {
        $invoice = $this->makeInvoice();

        app(CustomerInvoicePostingService::class)->post($invoice->fresh());

        $this->expectException(RuntimeException::class);
        app(CustomerInvoicePostingService::class)->post($invoice->fresh());
    }
}
