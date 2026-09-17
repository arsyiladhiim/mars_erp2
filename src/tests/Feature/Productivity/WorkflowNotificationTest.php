<?php

namespace Tests\Feature\Productivity;

use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use App\Models\Procurement\PurchaseOrder;
use App\Models\User;
use App\Models\Workflow\WorkflowRule;
use App\Services\Workflow\WorkflowEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkflowNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function makePurchaseOrder(Company $company, BusinessPartner $supplier, User $submitter): PurchaseOrder
    {
        $this->actingAs($submitter);

        return PurchaseOrder::create([
            'company_id' => $company->id, 'business_partner_id' => $supplier->id,
            'order_date' => '2026-01-05', 'grand_total' => 5_000_000, 'status' => 'draft',
        ]);
    }

    public function test_the_assigned_approver_is_notified_when_a_document_is_submitted(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $supplier = BusinessPartner::create(['code' => 'SUP1', 'name' => 'Supplier One', 'type' => 'supplier']);
        $submitter = User::create(['name' => 'Buyer', 'email' => 'buyer@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $rule = WorkflowRule::create(['company_id' => $company->id, 'name' => 'PO Approval', 'document_type' => 'purchase_order', 'priority' => 0]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);

        $po = $this->makePurchaseOrder($company, $supplier, $submitter);
        $po->submitForApproval();

        $this->assertSame(1, $manager->fresh()->notifications()->count());
        $this->assertSame(0, $submitter->fresh()->notifications()->count());
    }

    public function test_every_holder_of_a_role_routed_step_is_notified(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $supplier = BusinessPartner::create(['code' => 'SUP1', 'name' => 'Supplier One', 'type' => 'supplier']);
        $submitter = User::create(['name' => 'Buyer', 'email' => 'buyer2@test.local', 'password' => 'x', 'company_id' => $company->id]);

        Role::findOrCreate('Finance', 'web');
        $finance1 = User::create(['name' => 'Finance 1', 'email' => 'fin1@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $finance2 = User::create(['name' => 'Finance 2', 'email' => 'fin2@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $finance1->assignRole('Finance');
        $finance2->assignRole('Finance');

        $rule = WorkflowRule::create(['company_id' => $company->id, 'name' => 'PO Approval (role)', 'document_type' => 'purchase_order', 'priority' => 0]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Finance', 'approver_type' => 'role', 'approver_role' => 'Finance']);

        $po = $this->makePurchaseOrder($company, $supplier, $submitter);
        $po->submitForApproval();

        $this->assertSame(1, $finance1->fresh()->notifications()->count());
        $this->assertSame(1, $finance2->fresh()->notifications()->count());
    }

    public function test_only_the_first_sequence_approver_is_notified_until_their_step_clears(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $supplier = BusinessPartner::create(['code' => 'SUP1', 'name' => 'Supplier One', 'type' => 'supplier']);
        $submitter = User::create(['name' => 'Buyer', 'email' => 'buyer3@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr3@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $director = User::create(['name' => 'Director', 'email' => 'dir3@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $rule = WorkflowRule::create(['company_id' => $company->id, 'name' => 'Tiered', 'document_type' => 'purchase_order', 'priority' => 0]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);
        $rule->steps()->create(['sequence' => 2, 'name' => 'Director', 'approver_type' => 'user', 'approver_user_id' => $director->id]);

        $po = $this->makePurchaseOrder($company, $supplier, $submitter);
        $po->submitForApproval();

        $this->assertSame(1, $manager->fresh()->notifications()->count());
        $this->assertSame(0, $director->fresh()->notifications()->count());

        app(WorkflowEngine::class)->approve($po->fresh()->currentApproval(), $manager);

        $this->assertSame(1, $director->fresh()->notifications()->count());
    }

    public function test_the_submitter_is_notified_when_their_request_is_approved(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $supplier = BusinessPartner::create(['code' => 'SUP1', 'name' => 'Supplier One', 'type' => 'supplier']);
        $submitter = User::create(['name' => 'Buyer', 'email' => 'buyer4@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr4@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $rule = WorkflowRule::create(['company_id' => $company->id, 'name' => 'PO Approval', 'document_type' => 'purchase_order', 'priority' => 0]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);

        $po = $this->makePurchaseOrder($company, $supplier, $submitter);
        $po->submitForApproval();

        app(WorkflowEngine::class)->approve($po->fresh()->currentApproval(), $manager, 'Looks good');

        $this->assertSame(1, $submitter->fresh()->notifications()->count());
        $this->assertStringContainsString('approved', $submitter->fresh()->notifications()->first()->data['title']);
    }

    public function test_the_submitter_is_notified_when_their_request_is_rejected(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $supplier = BusinessPartner::create(['code' => 'SUP1', 'name' => 'Supplier One', 'type' => 'supplier']);
        $submitter = User::create(['name' => 'Buyer', 'email' => 'buyer5@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr5@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $rule = WorkflowRule::create(['company_id' => $company->id, 'name' => 'PO Approval', 'document_type' => 'purchase_order', 'priority' => 0]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);

        $po = $this->makePurchaseOrder($company, $supplier, $submitter);
        $po->submitForApproval();

        app(WorkflowEngine::class)->reject($po->fresh()->currentApproval(), $manager, 'Budget exceeded');

        $notification = $submitter->fresh()->notifications()->first();
        $this->assertStringContainsString('rejected', $notification->data['title']);
    }
}
