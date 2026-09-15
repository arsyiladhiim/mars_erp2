<?php

namespace App\Models\Workflow;

use App\Models\User;
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
}
