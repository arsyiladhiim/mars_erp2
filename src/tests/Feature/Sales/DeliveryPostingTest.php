<?php

namespace Tests\Feature\Sales;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\GlAccountMapping;
use App\Models\Master\BusinessPartner;
use App\Models\Master\Item;
use App\Models\Master\Uom;
use App\Models\Master\Warehouse;
use App\Models\Sales\Delivery;
use App\Models\Sales\SalesOrder;
use App\Services\Inventory\StockLedgerService;
use App\Services\Sales\DeliveryPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DeliveryPostingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Warehouse $warehouse;

    protected Item $item;

    protected BusinessPartner $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $fy = FiscalYear::create(['company_id' => $this->company->id, 'code' => 'FY2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open']);
        AccountingPeriod::create(['fiscal_year_id' => $fy->id, 'name' => 'January 2026', 'period_number' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'open']);

        $uom = Uom::create(['code' => 'PCS', 'name' => 'Pieces']);
        $this->warehouse = Warehouse::create(['company_id' => $this->company->id, 'code' => 'WH1', 'name' => 'Main Warehouse']);
        $this->customer = BusinessPartner::create(['code' => 'CUST1', 'name' => 'Customer One', 'type' => 'customer']);
        $this->item = Item::create(['sku' => 'ITEM1', 'name' => 'Widget', 'uom_id' => $uom->id]);

        $cogsAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '5101', 'name' => 'Cost of Goods Sold', 'account_type' => 'cogs']);
        $inventoryAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1301', 'name' => 'Inventory', 'account_type' => 'asset']);

        GlAccountMapping::ensureSeeded($this->company->id);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'cogs')->update(['chart_of_account_id' => $cogsAccount->id]);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'inventory')->update(['chart_of_account_id' => $inventoryAccount->id]);

        // Seed stock: 20 units @ 100.
        app(StockLedgerService::class)->receive($this->item->id, $this->warehouse->id, 20, 100, $this->company, '2026-01-05');
    }

    protected function makeApprovedSalesOrder(float $quantity): SalesOrder
    {
        $order = SalesOrder::create([
            'company_id' => $this->company->id, 'business_partner_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id, 'order_date' => '2026-01-05', 'status' => 'approved',
        ]);
        $order->lines()->create(['item_id' => $this->item->id, 'quantity' => $quantity, 'unit_price' => 150]);

        return $order->fresh('lines');
    }

    public function test_posting_a_full_delivery_reduces_stock_and_books_cogs(): void
    {
        $order = $this->makeApprovedSalesOrder(10);
        $orderLine = $order->lines->first();

        $delivery = Delivery::create([
            'company_id' => $this->company->id, 'sales_order_id' => $order->id,
            'business_partner_id' => $this->customer->id, 'warehouse_id' => $this->warehouse->id,
            'delivery_date' => '2026-01-10',
        ]);
        $delivery->lines()->create(['sales_order_line_id' => $orderLine->id, 'item_id' => $this->item->id, 'quantity' => 10]);

        $journalEntry = app(DeliveryPostingService::class)->post($delivery->fresh());

        $balance = app(StockLedgerService::class)->currentBalance($this->item->id, $this->warehouse->id);
        $this->assertSame(10.0, $balance['quantity']);

        $this->assertTrue($journalEntry->isBalanced());
        $this->assertSame('1000.00', $journalEntry->total_debit); // 10 * 100 average cost

        $this->assertSame('10.0000', $orderLine->fresh()->delivered_quantity);
        $this->assertSame('delivered', $order->fresh()->status);
        $this->assertSame('posted', $delivery->fresh()->status);
    }

    public function test_partial_delivery_leaves_sales_order_partially_delivered(): void
    {
        $order = $this->makeApprovedSalesOrder(10);
        $orderLine = $order->lines->first();

        $delivery = Delivery::create([
            'company_id' => $this->company->id, 'sales_order_id' => $order->id,
            'business_partner_id' => $this->customer->id, 'warehouse_id' => $this->warehouse->id,
            'delivery_date' => '2026-01-10',
        ]);
        $delivery->lines()->create(['sales_order_line_id' => $orderLine->id, 'item_id' => $this->item->id, 'quantity' => 4]);

        app(DeliveryPostingService::class)->post($delivery->fresh());

        $this->assertSame('partially_delivered', $order->fresh()->status);
    }

    public function test_delivering_against_an_unapproved_sales_order_is_blocked(): void
    {
        $order = $this->makeApprovedSalesOrder(10);
        $order->forceFill(['status' => 'draft'])->save();
        $orderLine = $order->lines->first();

        $delivery = Delivery::create([
            'company_id' => $this->company->id, 'sales_order_id' => $order->id,
            'business_partner_id' => $this->customer->id, 'warehouse_id' => $this->warehouse->id,
            'delivery_date' => '2026-01-10',
        ]);
        $delivery->lines()->create(['sales_order_line_id' => $orderLine->id, 'item_id' => $this->item->id, 'quantity' => 10]);

        $this->expectException(RuntimeException::class);
        app(DeliveryPostingService::class)->post($delivery->fresh());
    }

    public function test_delivering_more_than_available_stock_is_blocked(): void
    {
        $delivery = Delivery::create([
            'company_id' => $this->company->id, 'business_partner_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id, 'delivery_date' => '2026-01-10',
        ]);
        $delivery->lines()->create(['item_id' => $this->item->id, 'quantity' => 999]);

        $this->expectException(RuntimeException::class);
        app(DeliveryPostingService::class)->post($delivery->fresh());
    }

    public function test_posting_twice_is_rejected(): void
    {
        $delivery = Delivery::create([
            'company_id' => $this->company->id, 'business_partner_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id, 'delivery_date' => '2026-01-10',
        ]);
        $delivery->lines()->create(['item_id' => $this->item->id, 'quantity' => 5]);

        app(DeliveryPostingService::class)->post($delivery->fresh());

        $this->expectException(RuntimeException::class);
        app(DeliveryPostingService::class)->post($delivery->fresh());
    }
}
