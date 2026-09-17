<?php

namespace App\Models\Concerns;

use App\Models\Workflow\WorkflowApproval;
use App\Services\Workflow\WorkflowEngine;

/**
 * Opts a transactional document into the Approval Workflow Engine (PRD §23).
 * Host models must implement documentType() (matching a WorkflowRule's
 * document_type) and have `company_id` + `status` columns.
 */
trait Approvable
{
    public function approvals()
    {
        return $this->morphMany(WorkflowApproval::class, 'approvable')->orderBy('sequence');
    }

    public function currentApproval(): ?WorkflowApproval
    {
        return $this->approvals()->where('status', 'pending')->orderBy('sequence')->first();
    }

    public function submitForApproval(): void
    {
        app(WorkflowEngine::class)->submit($this);
    }

    abstract public static function documentType(): string;
}
