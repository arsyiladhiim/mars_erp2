<?php

declare(strict_types=1);

namespace App\Policies\Finance;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Finance\GlAccountMapping;
use Illuminate\Auth\Access\HandlesAuthorization;

class GlAccountMappingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GlAccountMapping');
    }

    public function view(AuthUser $authUser, GlAccountMapping $glAccountMapping): bool
    {
        return $authUser->can('View:GlAccountMapping');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GlAccountMapping');
    }

    public function update(AuthUser $authUser, GlAccountMapping $glAccountMapping): bool
    {
        return $authUser->can('Update:GlAccountMapping');
    }

    public function delete(AuthUser $authUser, GlAccountMapping $glAccountMapping): bool
    {
        return $authUser->can('Delete:GlAccountMapping');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:GlAccountMapping');
    }

    public function restore(AuthUser $authUser, GlAccountMapping $glAccountMapping): bool
    {
        return $authUser->can('Restore:GlAccountMapping');
    }

    public function forceDelete(AuthUser $authUser, GlAccountMapping $glAccountMapping): bool
    {
        return $authUser->can('ForceDelete:GlAccountMapping');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GlAccountMapping');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GlAccountMapping');
    }

    public function replicate(AuthUser $authUser, GlAccountMapping $glAccountMapping): bool
    {
        return $authUser->can('Replicate:GlAccountMapping');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GlAccountMapping');
    }

}