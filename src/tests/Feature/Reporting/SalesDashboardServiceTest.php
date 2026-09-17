<?php

namespace Tests\Feature\Reporting;

use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use App\Models\Master\Item;
use App\Models\Master\Uom;
use App\Models\Sales\CustomerInvoice;
use App\Services\Reporting\SalesDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_counts_and_sums_non_draft_invoices_in_range(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $customer = BusinessPartner::create(['code' => 'CUST1', 'name' => 'Customer One', 'type' => 'customer']);

        CustomerInvoice::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'invoice_date' => '2026-01-10', 'grand_total' => 1000, 'status' => 'posted']);
        CustomerInvoice::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'invoice_date' => '2026-01-15', 'grand_total' => 2000, 'status' => 'paid']);
        CustomerInvoice::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'invoice_date' => '2026-01-15', 'grand_total' => 9999, 'status' => 'draft']);
        CustomerInvoice::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'invoice_date' => '2025-06-01', 'grand_total' => 5000, 'status' => 'posted']);

        $summary = app(SalesDashboardService::class)->summary($company->id, '2026-01-01', '2026-01-31');

        $this->assertSame(2, $summary['invoice_count']);
        $this->assertSame(3000.0, $summary['total_sales']);
    }

    public function test_top_products_and_top_customers_rank_by_revenue(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $uom = Uom::create(['code' => 'PCS', 'name' => 'Pieces']);
        $customerA = BusinessPartner::create(['code' => 'CUST-A', 'name' => 'Customer A', 'type' => 'customer']);
        $customerB = BusinessPartner::create(['code' => 'CUST-B', 'name' => 'Customer B', 'type' => 'customer']);
        $bestSeller = Item::create(['sku' => 'BEST', 'name' => 'Best Seller', 'uom_id' => $uom->id]);
        $niche = Item::create(['sku' => 'NICHE', 'name' => 'Niche Item', 'uom_id' => $uom->id]);

        $invoiceA = CustomerInvoice::create(['company_id' => $company->id, 'business_partner_id' => $customerA->id, 'invoice_date' => '2026-01-10', 'grand_total' => 5000, 'status' => 'posted']);
        $invoiceA->lines()->create(['item_id' => $bestSeller->id, 'quantity' => 10, 'unit_price' => 500, 'line_total' => 5000]);

        $invoiceB = CustomerInvoice::create(['company_id' => $company->id, 'business_partner_id' => $customerB->id, 'invoice_date' => '2026-01-12', 'grand_total' => 100, 'status' => 'posted']);
        $invoiceB->lines()->create(['item_id' => $niche->id, 'quantity' => 1, 'unit_price' => 100, 'line_total' => 100]);

        $topProducts = app(SalesDashboardService::class)->topProducts($company->id, '2026-01-01', '2026-01-31');
        $topCustomers = app(SalesDashboardService::class)->topCustomers($company->id, '2026-01-01', '2026-01-31');

        $this->assertSame('BEST', $topProducts->first()['sku']);
        $this->assertSame('Customer A', $topCustomers->first()['customer']);
    }
}
