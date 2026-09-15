<?php

declare(strict_types=1);

namespace App\Policies\Workflow;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Workflow\WorkflowRule;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkflowRulePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WorkflowRule');
    }

    public function view(AuthUser $authUser, WorkflowRule $workflowRule): bool
    {
        return $authUser->can('View:WorkflowRule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WorkflowRule');
    }

    public function update(AuthUser $authUser, WorkflowRule $workflowRule): bool
    {
        return $authUser->can('Update:WorkflowRule');
    }

    public function delete(AuthUser $authUser, WorkflowRule $workflowRule): bool
    {
        return $authUser->can('Delete:WorkflowRule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WorkflowRule');
    }

    public function restore(AuthUser $authUser, WorkflowRule $workflowRule): bool
    {
        return $authUser->can('Restore:WorkflowRule');
    }

    public function forceDelete(AuthUser $authUser, WorkflowRule $workflowRule): bool
    {
        return $authUser->can('ForceDelete:WorkflowRule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WorkflowRule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WorkflowRule');
    }

    public function replicate(AuthUser $authUser, WorkflowRule $workflowRule): bool
    {
        return $authUser->can('Replicate:WorkflowRule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WorkflowRule');
    }

}