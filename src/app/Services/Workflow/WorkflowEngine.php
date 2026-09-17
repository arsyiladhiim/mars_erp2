<?php

namespace App\Services\Workflow;

use App\Models\Audit\AuditLog;
use App\Models\User;
use App\Models\Workflow\WorkflowApproval;
use App\Models\Workflow\WorkflowRule;
use App\Models\Workflow\WorkflowStep;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\Permission\Models\Role;

/**
 * Configurable Approval Workflow Engine (PRD §23). Matches a submitted
 * document against the company's active WorkflowRules (by document_type +
 * JSON conditions), materializes one WorkflowApproval row per WorkflowStep,
 * and drives the document's `status` as approvals/rejections come in.
 *
 * Sequential vs parallel is expressed purely through WorkflowStep.sequence:
 * steps sharing the same sequence number are "parallel" (all approvable at
 * once); a step is only actionable once every lower-sequence step for the
 * same document has been decided.
 */
class WorkflowEngine
{
    /**
     * Submit a document into the workflow. If no active rule matches, the
     * document is auto-approved immediately (no approval configured = no
     * gate) rather than getting stuck with nothing to action.
     */
    public function submit(Model $document): void
    {
        $rule = $this->matchRule($document);

        if (! $rule || $rule->steps->isEmpty()) {
            $document->forceFill(['status' => 'approved'])->save();

            AuditLog::record('auto_approved', get_class($document), $document->getKey());

            return;
        }

        DB::transaction(function () use ($document, $rule) {
            $document->forceFill(['status' => 'pending_approval'])->save();

            foreach ($rule->steps as $step) {
                $approval = WorkflowApproval::create([
                    'workflow_rule_id' => $rule->id,
                    'approvable_type' => get_class($document),
                    'approvable_id' => $document->getKey(),
                    'sequence' => $step->sequence,
                    'step_name' => $step->name,
                    'assigned_to' => $step->approver_type === 'user' ? $step->approver_user_id : null,
                    'status' => 'pending',
                    'due_at' => $step->deadline_hours ? now()->addHours($step->deadline_hours) : null,
                ]);

                // Only the first-sequence step(s) are actionable immediately —
                // don't notify approvers for later steps until it's their turn.
                if ($step->sequence === $rule->steps->min('sequence')) {
                    $this->notifyAssignees($approval, $step, $document);
                }
            }
        });

        AuditLog::record('submitted', get_class($document), $document->getKey());
    }

    /**
     * @throws RuntimeException if an earlier step is still pending (out-of-order approval).
     */
    public function approve(WorkflowApproval $approval, User $user, ?string $remarks = null): void
    {
        $this->assertActionable($approval);
        $this->assertAuthorized($approval, $user);

        DB::transaction(function () use ($approval, $user, $remarks) {
            $approval->update(['status' => 'approved', 'decided_at' => now(), 'remarks' => $remarks]);

            $document = $approval->approvable()->first();

            $pending = WorkflowApproval::query()
                ->where('approvable_type', $approval->approvable_type)
                ->where('approvable_id', $approval->approvable_id)
                ->where('status', 'pending')
                ->get();

            if ($pending->isEmpty()) {
                $document->forceFill(['status' => 'approved'])->save();
                $this->notifySubmitter($approval, $document, 'approved', $remarks);
            } else {
                $this->notifyNewlyActionableStep($pending, $document);
            }

            AuditLog::record('approval_decided', $approval->approvable_type, $approval->approvable_id, null, [
                'decision' => 'approved', 'step' => $approval->step_name, 'by' => $user->id, 'remarks' => $remarks,
            ]);
        });
    }

    public function reject(WorkflowApproval $approval, User $user, ?string $remarks = null): void
    {
        $this->assertActionable($approval);
        $this->assertAuthorized($approval, $user);

        DB::transaction(function () use ($approval, $user, $remarks) {
            $approval->update(['status' => 'rejected', 'decided_at' => now(), 'remarks' => $remarks]);

            $document = $approval->approvable()->first();
            $document->forceFill(['status' => 'rejected'])->save();

            WorkflowApproval::query()
                ->where('approvable_type', $approval->approvable_type)
                ->where('approvable_id', $approval->approvable_id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            $this->notifySubmitter($approval, $document, 'rejected', $remarks);

            AuditLog::record('approval_decided', $approval->approvable_type, $approval->approvable_id, null, [
                'decision' => 'rejected', 'step' => $approval->step_name, 'by' => $user->id, 'remarks' => $remarks,
            ]);
        });
    }

    /**
     * Notifies whichever approver(s) can now act once the lowest remaining
     * pending sequence has no earlier pending step blocking it.
     */
    protected function notifyNewlyActionableStep(Collection $pending, Model $document): void
    {
        $nextSequence = $pending->min('sequence');

        foreach ($pending->where('sequence', $nextSequence) as $nextApproval) {
            $step = WorkflowStep::query()
                ->where('workflow_rule_id', $nextApproval->workflow_rule_id)
                ->where('sequence', $nextSequence)
                ->first();

            if ($step) {
                $this->notifyAssignees($nextApproval, $step, $document);
            }
        }
    }

    /**
     * Notifies whichever user(s) can act on this step right now: the
     * specifically assigned user, or every holder of the step's role.
     */
    protected function notifyAssignees(WorkflowApproval $approval, WorkflowStep $step, Model $document): void
    {
        $recipients = $step->approver_type === 'user'
            ? User::query()->whereKey($step->approver_user_id)->get()
            : Role::where('name', $step->approver_role)->first()?->users ?? collect();

        $documentLabel = class_basename($document).' '.($document->number ?? "#{$document->getKey()}");

        foreach ($recipients as $recipient) {
            Notification::make()
                ->title('Approval requested')
                ->body("{$documentLabel} needs your approval — {$step->name}.")
                ->sendToDatabase($recipient);
        }
    }

    /**
     * Notifies whoever submitted the document, resolved from the 'submitted'
     * audit trail entry (no Approvable model has a reliable submitted_by
     * column of its own).
     */
    protected function notifySubmitter(WorkflowApproval $approval, Model $document, string $decision, ?string $remarks): void
    {
        $submitterId = AuditLog::query()
            ->where('entity_type', $approval->approvable_type)
            ->where('entity_id', $approval->approvable_id)
            ->where('action', 'submitted')
            ->latest('created_at')
            ->value('user_id');

        $submitter = $submitterId ? User::find($submitterId) : null;

        if (! $submitter) {
            return;
        }

        $documentLabel = class_basename($document).' '.($document->number ?? "#{$document->getKey()}");

        Notification::make()
            ->title($decision === 'approved' ? 'Request approved' : 'Request rejected')
            ->body("{$documentLabel} was {$decision} — {$approval->step_name}.".($remarks ? " \"{$remarks}\"" : ''))
            ->sendToDatabase($submitter);
    }

    protected function assertActionable(WorkflowApproval $approval): void
    {
        if ($approval->status !== 'pending') {
            throw new RuntimeException("This approval step has already been decided ({$approval->status}).");
        }

        $earlierPending = WorkflowApproval::query()
            ->where('approvable_type', $approval->approvable_type)
            ->where('approvable_id', $approval->approvable_id)
            ->where('sequence', '<', $approval->sequence)
            ->where('status', 'pending')
            ->exists();

        if ($earlierPending) {
            throw new RuntimeException('An earlier approval step is still pending — steps must be decided in sequence.');
        }
    }

    /**
     * @throws RuntimeException if $user is neither the specifically assigned
     *                           approver nor a holder of the step's approver_role.
     */
    protected function assertAuthorized(WorkflowApproval $approval, User $user): void
    {
        if ($approval->assigned_to) {
            if ((int) $approval->assigned_to !== (int) $user->id) {
                throw new RuntimeException('You are not the assigned approver for this step.');
            }

            return;
        }

        $step = WorkflowStep::query()
            ->where('workflow_rule_id', $approval->workflow_rule_id)
            ->where('sequence', $approval->sequence)
            ->first();

        if ($step?->approver_role && ! $user->hasRole($step->approver_role)) {
            throw new RuntimeException("You do not hold the \"{$step->approver_role}\" role required for this step.");
        }
    }

    protected function matchRule(Model $document): ?WorkflowRule
    {
        return WorkflowRule::query()
            ->where('company_id', $document->company_id)
            ->where('document_type', $document::documentType())
            ->where('is_active', true)
            ->orderBy('priority')
            ->with('steps')
            ->get()
            ->first(fn (WorkflowRule $rule) => $this->conditionsMatch($rule->conditions, $document));
    }

    /**
     * @param  array<string, mixed>|null  $conditions
     */
    protected function conditionsMatch(?array $conditions, Model $document): bool
    {
        if (empty($conditions)) {
            return true;
        }

        $amount = $document->grand_total ?? null;

        if (isset($conditions['amount_min']) && $amount !== null && (float) $amount < (float) $conditions['amount_min']) {
            return false;
        }

        if (isset($conditions['amount_max']) && $amount !== null && (float) $amount >= (float) $conditions['amount_max']) {
            return false;
        }

        foreach (['department_id', 'branch_id', 'cost_center_id'] as $field) {
            if (! empty($conditions[$field]) && (int) ($document->{$field} ?? 0) !== (int) $conditions[$field]) {
                return false;
            }
        }

        return true;
    }
}
