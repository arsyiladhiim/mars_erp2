<?php

namespace App\Models\Workflow;

use App\Models\Core\Company;
use Illuminate\Database\Eloquent\Model;

class WorkflowRule extends Model
{
    protected $table = 'workflow_rules';

    protected $fillable = ['company_id', 'name', 'document_type', 'conditions', 'priority', 'is_active'];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function steps()
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('sequence');
    }
}
