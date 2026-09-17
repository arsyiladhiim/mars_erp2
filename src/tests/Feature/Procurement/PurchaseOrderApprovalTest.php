<?php

namespace Tests\Feature\Procurement;

use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use App\Models\Procurement\PurchaseOrder;
use App\Models\User;
use App\Models\Workflow\WorkflowRule;
use App\Services\Workflow\WorkflowEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseOrderApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function makePurchaseOrder(Company $company, float $grandTotal): PurchaseOrder
    {
        $supplier = BusinessPartner::firstOrCreate(
            ['code' => 'SUP1'],
            ['name' => 'Supplier One', 'type' => 'supplier']
        );

        return PurchaseOrder::create([
            'company_id' => $company->id, 'business_partner_id' => $supplier->id,
            'order_date' => '2026-01-05', 'grand_total' => $grandTotal, 'status' => 'draft',
        ]);
    }

    public function test_submitting_a_purchase_order_generates_a_number_and_routes_to_the_assigned_approver(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $rule = WorkflowRule::create(['company_id' => $company->id, 'name' => 'PO Approval', 'document_type' => 'purchase_order', 'priority' => 0]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);

        $po = $this->makePurchaseOrder($company, 5_000_000);
        $this->assertStringStartsWith('PO-', $po->number);

        $po->submitForApproval();
        $this->assertSame('pending_approval', $po->fresh()->status);

        $approval = $po->fresh()->currentApproval();
        app(WorkflowEngine::class)->approve($approval, $manager);

        $this->assertSame('approved', $po->fresh()->status);
    }

    public function test_a_user_who_is_not_the_assigned_approver_cannot_approve(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr2@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $bystander = User::create(['name' => 'Bystander', 'email' => 'by2@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $rule = WorkflowRule::create(['company_id' => $company->id, 'name' => 'PO Approval', 'document_type' => 'purchase_order', 'priority' => 0]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);

        $po = $this->makePurchaseOrder($company, 5_000_000);
        $po->submitForApproval();

        $approval = $po->fresh()->currentApproval();

        $this->expectException(RuntimeException::class);
        app(WorkflowEngine::class)->approve($approval, $bystander);
    }

    public function test_a_role_routed_step_can_be_approved_by_anyone_holding_that_role(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        Role::findOrCreate('Finance', 'web');
        $financeUser = User::create(['name' => 'Finance User', 'email' => 'fin@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $financeUser->assignRole('Finance');
        $outsider = User::create(['name' => 'Outsider', 'email' => 'out@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $rule = WorkflowRule::create(['company_id' => $company->id, 'name' => 'PO Approval (role)', 'document_type' => 'purchase_order', 'priority' => 0]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Finance', 'approver_type' => 'role', 'approver_role' => 'Finance']);

        $po = $this->makePurchaseOrder($company, 5_000_000);
        $po->submitForApproval();
        $approval = $po->fresh()->currentApproval();

        $this->expectException(RuntimeException::class);
        app(WorkflowEngine::class)->approve($approval, $outsider);
    }

    public function test_a_role_routed_step_succeeds_for_a_user_holding_the_role(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        Role::findOrCreate('Finance', 'web');
        $financeUser = User::create(['name' => 'Finance User', 'email' => 'fin2@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $financeUser->assignRole('Finance');

        $rule = WorkflowRule::create(['company_id' => $company->id, 'name' => 'PO Approval (role)', 'document_type' => 'purchase_order', 'priority' => 0]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Finance', 'approver_type' => 'role', 'approver_role' => 'Finance']);

        $po = $this->makePurchaseOrder($company, 5_000_000);
        $po->submitForApproval();
        $approval = $po->fresh()->currentApproval();

        app(WorkflowEngine::class)->approve($approval, $financeUser);

        $this->assertSame('approved', $po->fresh()->status);
    }

    public function test_workflow_approval_visible_to_scope_includes_role_routed_and_directly_assigned_rows(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        Role::findOrCreate('Finance', 'web');
        $financeUser = User::create(['name' => 'Finance User', 'email' => 'fin3@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $financeUser->assignRole('Finance');
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr3@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $roleRule = WorkflowRule::create(['company_id' => $company->id, 'name' => 'Role Rule', 'document_type' => 'purchase_order', 'priority' => 0, 'conditions' => ['amount_max' => 1_000_000]]);
        $roleRule->steps()->create(['sequence' => 1, 'name' => 'Finance', 'approver_type' => 'role', 'approver_role' => 'Finance']);

        $userRule = WorkflowRule::create(['company_id' => $company->id, 'name' => 'User Rule', 'document_type' => 'purchase_order', 'priority' => 1, 'conditions' => ['amount_min' => 1_000_000]]);
        $userRule->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);

        $smallPo = $this->makePurchaseOrder($company, 500_000);
        $smallPo->submitForApproval();

        $largePo = $this->makePurchaseOrder($company, 2_000_000);
        $largePo->submitForApproval();

        $this->assertSame(1, \App\Models\Workflow\WorkflowApproval::visibleTo($financeUser)->count());
        $this->assertSame(1, \App\Models\Workflow\WorkflowApproval::visibleTo($manager)->count());
    }
}
