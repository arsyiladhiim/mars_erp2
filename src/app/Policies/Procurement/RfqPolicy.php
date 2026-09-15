<?php

declare(strict_types=1);

namespace App\Policies\Procurement;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Procurement\Rfq;
use Illuminate\Auth\Access\HandlesAuthorization;

class RfqPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Rfq');
    }

    public function view(AuthUser $authUser, Rfq $rfq): bool
    {
        return $authUser->can('View:Rfq');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Rfq');
    }

    public function update(AuthUser $authUser, Rfq $rfq): bool
    {
        return $authUser->can('Update:Rfq');
    }

    public function delete(AuthUser $authUser, Rfq $rfq): bool
    {
        return $authUser->can('Delete:Rfq');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Rfq');
    }

    public function restore(AuthUser $authUser, Rfq $rfq): bool
    {
        return $authUser->can('Restore:Rfq');
    }

    public function forceDelete(AuthUser $authUser, Rfq $rfq): bool
    {
        return $authUser->can('ForceDelete:Rfq');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Rfq');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Rfq');
    }

    public function replicate(AuthUser $authUser, Rfq $rfq): bool
    {
        return $authUser->can('Replicate:Rfq');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Rfq');
    }

}