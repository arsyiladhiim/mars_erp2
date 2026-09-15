<?php

declare(strict_types=1);

namespace App\Policies\Core;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Core\ProfitCenter;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfitCenterPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProfitCenter');
    }

    public function view(AuthUser $authUser, ProfitCenter $profitCenter): bool
    {
        return $authUser->can('View:ProfitCenter');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProfitCenter');
    }

    public function update(AuthUser $authUser, ProfitCenter $profitCenter): bool
    {
        return $authUser->can('Update:ProfitCenter');
    }

    public function delete(AuthUser $authUser, ProfitCenter $profitCenter): bool
    {
        return $authUser->can('Delete:ProfitCenter');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ProfitCenter');
    }

    public function restore(AuthUser $authUser, ProfitCenter $profitCenter): bool
    {
        return $authUser->can('Restore:ProfitCenter');
    }

    public function forceDelete(AuthUser $authUser, ProfitCenter $profitCenter): bool
    {
        return $authUser->can('ForceDelete:ProfitCenter');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProfitCenter');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProfitCenter');
    }

    public function replicate(AuthUser $authUser, ProfitCenter $profitCenter): bool
    {
        return $authUser->can('Replicate:ProfitCenter');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProfitCenter');
    }

}