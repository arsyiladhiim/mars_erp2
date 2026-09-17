<?php

namespace Tests\Feature\Procurement;

use App\Models\Core\Company;
use App\Models\Finance\SupplierInvoice;
use App\Models\Inventory\GoodsReceipt;
use App\Models\Master\BusinessPartner;
use App\Models\Master\Warehouse;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\SupplierQuotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_request_is_assigned_a_pr_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $requester = User::create(['name' => 'Req', 'email' => 'req@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $pr = PurchaseRequest::create(['company_id' => $company->id, 'requester_id' => $requester->id]);

        $this->assertStringStartsWith('PR-', $pr->number);
    }

    public function test_rfq_is_assigned_an_rfq_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $rfq = Rfq::create(['company_id' => $company->id]);

        $this->assertStringStartsWith('RFQ-', $rfq->number);
    }

    public function test_supplier_quotation_is_assigned_an_sq_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $supplier = BusinessPartner::create(['code' => 'SUP1', 'name' => 'Supplier One', 'type' => 'supplier']);

        $sq = SupplierQuotation::create(['company_id' => $company->id, 'business_partner_id' => $supplier->id]);

        $this->assertStringStartsWith('SQ-', $sq->number);
    }

    public function test_goods_receipt_is_assigned_a_gr_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $warehouse = Warehouse::create(['company_id' => $company->id, 'code' => 'WH1', 'name' => 'Main Warehouse']);

        $gr = GoodsReceipt::create(['company_id' => $company->id, 'warehouse_id' => $warehouse->id, 'receipt_date' => '2026-01-10']);

        $this->assertStringStartsWith('GR-', $gr->number);
    }

    public function test_supplier_invoice_is_assigned_a_sinv_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $supplier = BusinessPartner::create(['code' => 'SUP1', 'name' => 'Supplier One', 'type' => 'supplier']);

        $invoice = SupplierInvoice::create(['company_id' => $company->id, 'business_partner_id' => $supplier->id, 'invoice_date' => '2026-01-12']);

        $this->assertStringStartsWith('SINV-', $invoice->number);
    }

    public function test_an_explicitly_provided_number_is_not_overridden(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $warehouse = Warehouse::create(['company_id' => $company->id, 'code' => 'WH1', 'name' => 'Main Warehouse']);

        $gr = GoodsReceipt::create([
            'company_id' => $company->id, 'warehouse_id' => $warehouse->id,
            'receipt_date' => '2026-01-10', 'number' => 'CUSTOM-001',
        ]);

        $this->assertSame('CUSTOM-001', $gr->number);
    }
}
