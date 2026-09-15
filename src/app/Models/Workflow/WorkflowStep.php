<?php

namespace App\Models\Workflow;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    protected $table = 'workflow_steps';

    protected $fillable = [
        'workflow_rule_id', 'sequence', 'name', 'approver_type',
        'approver_role', 'approver_user_id', 'mode', 'deadline_hours',
    ];

    public function rule()
    {
        return $this->belongsTo(WorkflowRule::class, 'workflow_rule_id');
    }

    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}
