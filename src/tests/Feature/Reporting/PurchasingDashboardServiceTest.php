<?php

namespace Tests\Feature\Reporting;

use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use App\Models\Procurement\PurchaseOrder;
use App\Services\Reporting\PurchasingDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasingDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_outstanding_purchase_orders_excludes_closed_and_cancelled(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $supplier = BusinessPartner::create(['code' => 'SUP1', 'name' => 'Supplier One', 'type' => 'supplier']);

        PurchaseOrder::create(['company_id' => $company->id, 'business_partner_id' => $supplier->id, 'order_date' => '2026-01-05', 'grand_total' => 1000, 'status' => 'approved']);
        PurchaseOrder::create(['company_id' => $company->id, 'business_partner_id' => $supplier->id, 'order_date' => '2026-01-05', 'grand_total' => 2000, 'status' => 'closed']);
        PurchaseOrder::create(['company_id' => $company->id, 'business_partner_id' => $supplier->id, 'order_date' => '2026-01-05', 'grand_total' => 3000, 'status' => 'cancelled']);

        $outstanding = app(PurchasingDashboardService::class)->outstandingPurchaseOrders($company->id);

        $this->assertCount(1, $outstanding);
        $this->assertSame(1000.0, $outstanding->first()['grand_total']);
    }

    public function test_supplier_spend_ranks_by_total_and_excludes_drafts(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $big = BusinessPartner::create(['code' => 'SUP-BIG', 'name' => 'Big Supplier', 'type' => 'supplier']);
        $small = BusinessPartner::create(['code' => 'SUP-SMALL', 'name' => 'Small Supplier', 'type' => 'supplier']);

        PurchaseOrder::create(['company_id' => $company->id, 'business_partner_id' => $big->id, 'order_date' => '2026-01-05', 'grand_total' => 5000, 'status' => 'approved']);
        PurchaseOrder::create(['company_id' => $company->id, 'business_partner_id' => $small->id, 'order_date' => '2026-01-05', 'grand_total' => 1000, 'status' => 'approved']);
        PurchaseOrder::create(['company_id' => $company->id, 'business_partner_id' => $big->id, 'order_date' => '2026-01-05', 'grand_total' => 9999, 'status' => 'draft']);

        $spend = app(PurchasingDashboardService::class)->supplierSpend($company->id, '2026-01-01', '2026-01-31');

        $this->assertSame('Big Supplier', $spend->first()['supplier']);
        $this->assertSame(5000.0, $spend->first()['total_spend']);
        $this->assertSame(1, $spend->first()['order_count']);
    }
}
