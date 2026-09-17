<?php

namespace Tests\Feature\Finance;

use App\Models\Core\Company;
use App\Models\Finance\SupplierInvoice;
use App\Models\Master\BusinessPartner;
use App\Models\Sales\CustomerInvoice;
use App\Services\Finance\AgingReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgingReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_invoices_are_bucketed_by_days_overdue(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $customer = BusinessPartner::create(['code' => 'CUST1', 'name' => 'Customer One', 'type' => 'customer']);

        // asOf = 2026-06-15. Due 2026-06-05 => 10 days overdue => "1-30".
        CustomerInvoice::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'invoice_date' => '2026-05-01', 'due_date' => '2026-06-05', 'grand_total' => 100, 'status' => 'posted']);
        // Due 2026-05-01 => 45 days overdue => "31-60".
        CustomerInvoice::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'invoice_date' => '2026-04-01', 'due_date' => '2026-05-01', 'grand_total' => 200, 'status' => 'posted']);
        // Due 2026-07-01 => not yet due => "Current".
        CustomerInvoice::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'invoice_date' => '2026-06-01', 'due_date' => '2026-07-01', 'grand_total' => 300, 'status' => 'posted']);
        // Fully paid — must be excluded.
        CustomerInvoice::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'invoice_date' => '2026-01-01', 'due_date' => '2026-01-10', 'grand_total' => 400, 'paid_amount' => 400, 'status' => 'paid']);
        // Cancelled — must be excluded.
        CustomerInvoice::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'invoice_date' => '2026-01-01', 'due_date' => '2026-01-10', 'grand_total' => 500, 'status' => 'cancelled']);

        $asOf = '2026-06-15';

        $rows = app(AgingReportService::class)->customerAging($company->id, $asOf);

        $this->assertCount(3, $rows);

        $bucketByAmount = $rows->keyBy('outstanding');
        $this->assertSame('1-30', $bucketByAmount[100.0]['bucket']);
        $this->assertSame('31-60', $bucketByAmount[200.0]['bucket']);
        $this->assertSame('Current', $bucketByAmount[300.0]['bucket']);
    }

    public function test_supplier_invoices_are_bucketed_and_reflect_partial_payments(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $supplier = BusinessPartner::create(['code' => 'SUP1', 'name' => 'Supplier One', 'type' => 'supplier']);

        SupplierInvoice::create([
            'company_id' => $company->id, 'business_partner_id' => $supplier->id, 'invoice_date' => '2026-01-01',
            'due_date' => '2025-11-01', 'grand_total' => 1000, 'paid_amount' => 400, 'status' => 'partially_paid',
        ]);

        $rows = app(AgingReportService::class)->supplierAging($company->id, '2026-02-01');

        $this->assertCount(1, $rows);
        $this->assertSame(600.0, $rows[0]['outstanding']);
        $this->assertSame('90+', $rows[0]['bucket']);
    }
}
