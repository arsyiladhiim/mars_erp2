<?php

namespace Tests\Feature\Finance;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Finance\BankAccount;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\OutgoingPayment;
use App\Models\Finance\SupplierInvoice;
use App\Models\Master\BusinessPartner;
use App\Services\Finance\OutgoingPaymentPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class OutgoingPaymentPostingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected SupplierInvoice $invoice;

    protected BankAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $fy = FiscalYear::create(['company_id' => $this->company->id, 'code' => 'FY2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open']);
        AccountingPeriod::create(['fiscal_year_id' => $fy->id, 'name' => 'January 2026', 'period_number' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'open']);

        $supplier = BusinessPartner::create(['code' => 'SUP1', 'name' => 'Supplier One', 'type' => 'supplier']);

        $apAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '2101', 'name' => 'Accounts Payable', 'account_type' => 'liability']);
        $bankChartAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1101', 'name' => 'Bank BCA', 'account_type' => 'asset']);

        GlAccountMapping::ensureSeeded($this->company->id);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'accounts_payable')->update(['chart_of_account_id' => $apAccount->id]);

        $this->bankAccount = BankAccount::create([
            'company_id' => $this->company->id, 'chart_of_account_id' => $bankChartAccount->id,
            'account_name' => 'Main BCA', 'account_number' => '123456', 'bank_name' => 'BCA',
        ]);

        $this->invoice = SupplierInvoice::create([
            'company_id' => $this->company->id, 'business_partner_id' => $supplier->id, 'invoice_date' => '2026-01-12',
            'subtotal' => 1000, 'grand_total' => 1000, 'status' => 'posted',
        ]);
    }

    protected function makePayment(float $amount, array $overrides = []): OutgoingPayment
    {
        return OutgoingPayment::create(array_merge([
            'company_id' => $this->company->id, 'business_partner_id' => $this->invoice->business_partner_id,
            'supplier_invoice_id' => $this->invoice->id, 'bank_account_id' => $this->bankAccount->id,
            'payment_date' => '2026-01-15', 'amount' => $amount,
        ], $overrides));
    }

    public function test_number_is_auto_generated(): void
    {
        $payment = $this->makePayment(100);

        $this->assertStringStartsWith('PAY-', $payment->number);
    }

    public function test_a_full_payment_marks_the_invoice_paid_and_books_the_disbursement(): void
    {
        $payment = $this->makePayment(1000);

        $journalEntry = app(OutgoingPaymentPostingService::class)->post($payment->fresh());

        $this->assertTrue($journalEntry->isBalanced());
        $this->assertSame('1000.00', $journalEntry->total_debit);
        $this->assertSame('paid', $this->invoice->fresh()->status);
        $this->assertSame('1000.00', $this->invoice->fresh()->paid_amount);
        $this->assertSame('posted', $payment->fresh()->status);

        $debitLine = $journalEntry->lines()->where('debit', '>', 0)->first();
        $this->assertSame('Accounts Payable', $debitLine->chartOfAccount->name);
    }

    public function test_a_partial_payment_marks_the_invoice_partially_paid(): void
    {
        $payment = $this->makePayment(400);

        app(OutgoingPaymentPostingService::class)->post($payment->fresh());

        $this->assertSame('partially_paid', $this->invoice->fresh()->status);
        $this->assertSame('400.00', $this->invoice->fresh()->paid_amount);
    }

    public function test_overpaying_the_outstanding_balance_is_blocked(): void
    {
        $payment = $this->makePayment(5000);

        $this->expectException(RuntimeException::class);
        app(OutgoingPaymentPostingService::class)->post($payment->fresh());
    }

    public function test_a_payment_without_an_invoice_is_blocked(): void
    {
        $payment = $this->makePayment(500, ['supplier_invoice_id' => null]);

        $this->expectException(RuntimeException::class);
        app(OutgoingPaymentPostingService::class)->post($payment->fresh());
    }

    public function test_posting_twice_is_rejected(): void
    {
        $payment = $this->makePayment(300);

        app(OutgoingPaymentPostingService::class)->post($payment->fresh());

        $this->expectException(RuntimeException::class);
        app(OutgoingPaymentPostingService::class)->post($payment->fresh());
    }
}
