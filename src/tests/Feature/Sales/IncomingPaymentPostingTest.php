<?php

namespace Tests\Feature\Sales;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Finance\BankAccount;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\IncomingPayment;
use App\Models\Master\BusinessPartner;
use App\Models\Sales\CustomerInvoice;
use App\Services\Sales\IncomingPaymentPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class IncomingPaymentPostingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected CustomerInvoice $invoice;

    protected BankAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $fy = FiscalYear::create(['company_id' => $this->company->id, 'code' => 'FY2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open']);
        AccountingPeriod::create(['fiscal_year_id' => $fy->id, 'name' => 'January 2026', 'period_number' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'open']);

        $customer = BusinessPartner::create(['code' => 'CUST1', 'name' => 'Customer One', 'type' => 'customer']);

        $arAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1201', 'name' => 'Accounts Receivable', 'account_type' => 'asset']);
        $bankChartAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1101', 'name' => 'Bank BCA', 'account_type' => 'asset']);

        GlAccountMapping::ensureSeeded($this->company->id);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'accounts_receivable')->update(['chart_of_account_id' => $arAccount->id]);

        $this->bankAccount = BankAccount::create([
            'company_id' => $this->company->id, 'chart_of_account_id' => $bankChartAccount->id,
            'account_name' => 'Main BCA', 'account_number' => '123456', 'bank_name' => 'BCA',
        ]);

        $this->invoice = CustomerInvoice::create([
            'company_id' => $this->company->id, 'business_partner_id' => $customer->id, 'invoice_date' => '2026-01-12',
            'subtotal' => 1000, 'grand_total' => 1000, 'status' => 'posted',
        ]);
    }

    protected function makePayment(float $amount, array $overrides = []): IncomingPayment
    {
        return IncomingPayment::create(array_merge([
            'company_id' => $this->company->id, 'business_partner_id' => $this->invoice->business_partner_id,
            'customer_invoice_id' => $this->invoice->id, 'bank_account_id' => $this->bankAccount->id,
            'payment_date' => '2026-01-15', 'amount' => $amount,
        ], $overrides));
    }

    public function test_a_full_payment_marks_the_invoice_paid_and_books_the_receipt(): void
    {
        $payment = $this->makePayment(1000);

        $journalEntry = app(IncomingPaymentPostingService::class)->post($payment->fresh());

        $this->assertTrue($journalEntry->isBalanced());
        $this->assertSame('1000.00', $journalEntry->total_debit);
        $this->assertSame('paid', $this->invoice->fresh()->status);
        $this->assertSame('1000.00', $this->invoice->fresh()->paid_amount);
        $this->assertSame('posted', $payment->fresh()->status);
    }

    public function test_a_partial_payment_marks_the_invoice_partially_paid(): void
    {
        $payment = $this->makePayment(400);

        app(IncomingPaymentPostingService::class)->post($payment->fresh());

        $this->assertSame('partially_paid', $this->invoice->fresh()->status);
        $this->assertSame('400.00', $this->invoice->fresh()->paid_amount);
    }

    public function test_overpaying_the_outstanding_balance_is_blocked(): void
    {
        $payment = $this->makePayment(5000);

        $this->expectException(RuntimeException::class);
        app(IncomingPaymentPostingService::class)->post($payment->fresh());
    }

    public function test_a_payment_without_an_invoice_is_blocked(): void
    {
        $payment = $this->makePayment(500, ['customer_invoice_id' => null]);

        $this->expectException(RuntimeException::class);
        app(IncomingPaymentPostingService::class)->post($payment->fresh());
    }

    public function test_a_payment_without_a_bank_account_is_blocked(): void
    {
        $payment = $this->makePayment(500, ['bank_account_id' => null]);

        $this->expectException(RuntimeException::class);
        app(IncomingPaymentPostingService::class)->post($payment->fresh());
    }

    public function test_posting_twice_is_rejected(): void
    {
        $payment = $this->makePayment(300);

        app(IncomingPaymentPostingService::class)->post($payment->fresh());

        $this->expectException(RuntimeException::class);
        app(IncomingPaymentPostingService::class)->post($payment->fresh());
    }
}
