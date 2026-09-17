<?php

namespace Tests\Feature\Sales;

use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Models\Workflow\WorkflowRule;
use App\Services\Workflow\WorkflowEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * WorkflowEngine's mechanics (sequencing, authorization, role-routing) are
 * already exhaustively covered by Tests\Feature\Foundation\WorkflowEngineTest
 * and Tests\Feature\Procurement\PurchaseOrderApprovalTest — this just
 * confirms SalesOrder is correctly wired into the same generic engine.
 */
class SalesOrderApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_a_sales_order_generates_a_number_and_routes_to_the_assigned_approver(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $customer = BusinessPartner::create(['code' => 'CUST1', 'name' => 'Customer One', 'type' => 'customer']);
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $rule = WorkflowRule::create(['company_id' => $company->id, 'name' => 'SO Approval', 'document_type' => 'sales_order', 'priority' => 0]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);

        $order = SalesOrder::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'order_date' => '2026-01-05', 'grand_total' => 5_000_000]);
        $this->assertStringStartsWith('SO-', $order->number);

        $order->submitForApproval();
        $this->assertSame('pending_approval', $order->fresh()->status);

        app(WorkflowEngine::class)->approve($order->fresh()->currentApproval(), $manager);

        $this->assertSame('approved', $order->fresh()->status);
    }

    public function test_submitting_without_a_matching_rule_auto_approves(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $customer = BusinessPartner::create(['code' => 'CUST1', 'name' => 'Customer One', 'type' => 'customer']);

        $order = SalesOrder::create(['company_id' => $company->id, 'business_partner_id' => $customer->id, 'order_date' => '2026-01-05']);

        $order->submitForApproval();

        $this->assertSame('approved', $order->fresh()->status);
    }
}
