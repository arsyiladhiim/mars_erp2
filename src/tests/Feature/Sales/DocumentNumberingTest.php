<?php

namespace Tests\Feature\Sales;

use App\Models\Core\Company;
use App\Models\Finance\IncomingPayment;
use App\Models\Master\BusinessPartner;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Delivery;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesQuotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_quotation_is_assigned_a_quo_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $customer = BusinessPartner::create(['code' => 'CUST1', 'name' => 'Customer One', 'type' => 'customer']);

        $quotation = SalesQuotation::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'quotation_date' => '2026-01-05']);

        $this->assertStringStartsWith('QUO-', $quotation->number);
    }

    public function test_sales_order_is_assigned_an_so_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $customer = BusinessPartner::create(['code' => 'CUST1', 'name' => 'Customer One', 'type' => 'customer']);

        $order = SalesOrder::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'order_date' => '2026-01-05']);

        $this->assertStringStartsWith('SO-', $order->number);
    }

    public function test_delivery_is_assigned_a_do_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $customer = BusinessPartner::create(['code' => 'CUST1', 'name' => 'Customer One', 'type' => 'customer']);
        $warehouse = \App\Models\Master\Warehouse::create(['company_id' => $company->id, 'code' => 'WH1', 'name' => 'Main Warehouse']);

        $delivery = Delivery::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'warehouse_id' => $warehouse->id, 'delivery_date' => '2026-01-10']);

        $this->assertStringStartsWith('DO-', $delivery->number);
    }

    public function test_customer_invoice_is_assigned_an_inv_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $customer = BusinessPartner::create(['code' => 'CUST1', 'name' => 'Customer One', 'type' => 'customer']);

        $invoice = CustomerInvoice::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'invoice_date' => '2026-01-12']);

        $this->assertStringStartsWith('INV-', $invoice->number);
    }

    public function test_incoming_payment_is_assigned_a_rcp_prefixed_number(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $customer = BusinessPartner::create(['code' => 'CUST1', 'name' => 'Customer One', 'type' => 'customer']);

        $payment = IncomingPayment::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'payment_date' => '2026-01-15']);

        $this->assertStringStartsWith('RCP-', $payment->number);
    }
}
