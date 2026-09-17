<?php

namespace Tests\Feature\Foundation;

use App\Models\Concerns\Approvable;
use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Company;
use App\Models\User;
use App\Models\Workflow\WorkflowRule;
use App\Services\Workflow\WorkflowEngine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

/**
 * Exercises App\Services\Workflow\WorkflowEngine against a throwaway
 * "test_documents" table/model — kept out of the real schema entirely — so
 * the generic engine can be proven correct independently of any specific
 * Phase 2+ business document.
 */
class WorkflowEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->decimal('grand_total', 18, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_documents');

        parent::tearDown();
    }

    protected function makeDocument(int $companyId, float $grandTotal = 0): Model
    {
        return WorkflowEngineTestDocument::create(['company_id' => $companyId, 'grand_total' => $grandTotal]);
    }

    public function test_document_is_auto_approved_when_no_rule_matches(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $document = $this->makeDocument($company->id, 1000);

        app(WorkflowEngine::class)->submit($document);

        $this->assertSame('approved', $document->fresh()->status);
        $this->assertSame(0, $document->fresh()->approvals()->count());
    }

    public function test_single_step_rule_requires_one_approval(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $rule = WorkflowRule::create([
            'company_id' => $company->id, 'name' => 'Default PO Approval',
            'document_type' => 'test_document', 'priority' => 0,
        ]);
        $rule->steps()->create([
            'sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user',
            'approver_user_id' => $manager->id, 'mode' => 'sequential',
        ]);

        $document = $this->makeDocument($company->id, 5000);

        app(WorkflowEngine::class)->submit($document);
        $document->refresh();

        $this->assertSame('pending_approval', $document->status);
        $this->assertSame(1, $document->approvals()->count());

        $approval = $document->currentApproval();
        app(WorkflowEngine::class)->approve($approval, $manager);

        $this->assertSame('approved', $document->fresh()->status);
        $this->assertSame('approved', $approval->fresh()->status);
    }

    public function test_sequential_steps_must_be_decided_in_order(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr2@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $director = User::create(['name' => 'Director', 'email' => 'dir@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $rule = WorkflowRule::create([
            'company_id' => $company->id, 'name' => 'Tiered Approval',
            'document_type' => 'test_document', 'priority' => 0,
        ]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);
        $rule->steps()->create(['sequence' => 2, 'name' => 'Director', 'approver_type' => 'user', 'approver_user_id' => $director->id]);

        $document = $this->makeDocument($company->id, 200_000_000);
        app(WorkflowEngine::class)->submit($document);

        $directorApproval = $document->approvals()->where('sequence', 2)->first();

        $this->expectException(RuntimeException::class);
        app(WorkflowEngine::class)->approve($directorApproval, $director);
    }

    public function test_full_sequential_chain_approves_document_only_after_last_step(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr3@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $director = User::create(['name' => 'Director', 'email' => 'dir3@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $rule = WorkflowRule::create([
            'company_id' => $company->id, 'name' => 'Tiered Approval',
            'document_type' => 'test_document', 'priority' => 0,
        ]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);
        $rule->steps()->create(['sequence' => 2, 'name' => 'Director', 'approver_type' => 'user', 'approver_user_id' => $director->id]);

        $document = $this->makeDocument($company->id, 200_000_000);
        app(WorkflowEngine::class)->submit($document);

        $engine = app(WorkflowEngine::class);
        $engine->approve($document->approvals()->where('sequence', 1)->first(), $manager);

        $this->assertSame('pending_approval', $document->fresh()->status, 'still waiting on director');

        $engine->approve($document->fresh()->approvals()->where('sequence', 2)->first(), $director);

        $this->assertSame('approved', $document->fresh()->status);
    }

    public function test_rejection_cancels_remaining_pending_steps_and_rejects_document(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr4@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $director = User::create(['name' => 'Director', 'email' => 'dir4@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $rule = WorkflowRule::create([
            'company_id' => $company->id, 'name' => 'Tiered Approval',
            'document_type' => 'test_document', 'priority' => 0,
        ]);
        $rule->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);
        $rule->steps()->create(['sequence' => 2, 'name' => 'Director', 'approver_type' => 'user', 'approver_user_id' => $director->id]);

        $document = $this->makeDocument($company->id, 200_000_000);
        app(WorkflowEngine::class)->submit($document);

        app(WorkflowEngine::class)->reject($document->approvals()->where('sequence', 1)->first(), $manager, 'Not needed');

        $this->assertSame('rejected', $document->fresh()->status);
        $this->assertSame('cancelled', $document->fresh()->approvals()->where('sequence', 2)->first()->status);
    }

    public function test_amount_condition_selects_the_matching_rule(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $manager = User::create(['name' => 'Manager', 'email' => 'mgr5@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $director = User::create(['name' => 'Director', 'email' => 'dir5@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $small = WorkflowRule::create([
            'company_id' => $company->id, 'name' => 'Small', 'document_type' => 'test_document',
            'conditions' => ['amount_max' => 5_000_000], 'priority' => 1,
        ]);
        $small->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);

        $large = WorkflowRule::create([
            'company_id' => $company->id, 'name' => 'Large', 'document_type' => 'test_document',
            'conditions' => ['amount_min' => 5_000_000], 'priority' => 2,
        ]);
        $large->steps()->create(['sequence' => 1, 'name' => 'Manager', 'approver_type' => 'user', 'approver_user_id' => $manager->id]);
        $large->steps()->create(['sequence' => 2, 'name' => 'Director', 'approver_type' => 'user', 'approver_user_id' => $director->id]);

        $smallDoc = $this->makeDocument($company->id, 1_000_000);
        app(WorkflowEngine::class)->submit($smallDoc);
        $this->assertSame(1, $smallDoc->approvals()->count());

        $largeDoc = $this->makeDocument($company->id, 10_000_000);
        app(WorkflowEngine::class)->submit($largeDoc);
        $this->assertSame(2, $largeDoc->approvals()->count());
    }
}

class WorkflowEngineTestDocument extends Model
{
    use Approvable, HasAuditTrail;

    protected $table = 'test_documents';

    protected $fillable = ['company_id', 'grand_total', 'status'];

    public static function documentType(): string
    {
        return 'test_document';
    }
}
