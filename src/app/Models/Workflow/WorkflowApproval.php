<?php

namespace App\Models\Workflow;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WorkflowApproval extends Model
{
    protected $table = 'workflow_approvals';

    protected $fillable = [
        'workflow_rule_id', 'approvable_type', 'approvable_id', 'sequence', 'step_name',
        'assigned_to', 'delegated_to', 'status', 'remarks', 'due_at', 'decided_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function approvable()
    {
        return $this->morphTo();
    }

    public function rule()
    {
        return $this->belongsTo(WorkflowRule::class, 'workflow_rule_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function delegatedTo()
    {
        return $this->belongsTo(User::class, 'delegated_to');
    }

    /**
     * Pending approvals actionable by $user: either specifically assigned to
     * them, or role-routed (assigned_to null) where $user holds the
     * WorkflowStep's approver_role for that rule+sequence.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $roles = $user->getRoleNames();

        return $query->where('status', 'pending')->where(function (Builder $q) use ($user, $roles) {
            $q->where('assigned_to', $user->id)
                ->orWhere(function (Builder $q2) use ($roles) {
                    $q2->whereNull('assigned_to')
                        ->whereExists(function ($sub) use ($roles) {
                            $sub->selectRaw('1')
                                ->from('workflow_steps')
                                ->whereColumn('workflow_steps.workflow_rule_id', 'workflow_approvals.workflow_rule_id')
                                ->whereColumn('workflow_steps.sequence', 'workflow_approvals.sequence')
                                ->whereIn('workflow_steps.approver_role', $roles);
                        });
                });
        });
    }
}
