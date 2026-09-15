<?php

declare(strict_types=1);

namespace App\Policies\Productivity;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Productivity\Memo;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemoPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Memo');
    }

    public function view(AuthUser $authUser, Memo $memo): bool
    {
        return $authUser->can('View:Memo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Memo');
    }

    public function update(AuthUser $authUser, Memo $memo): bool
    {
        return $authUser->can('Update:Memo');
    }

    public function delete(AuthUser $authUser, Memo $memo): bool
    {
        return $authUser->can('Delete:Memo');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Memo');
    }

    public function restore(AuthUser $authUser, Memo $memo): bool
    {
        return $authUser->can('Restore:Memo');
    }

    public function forceDelete(AuthUser $authUser, Memo $memo): bool
    {
        return $authUser->can('ForceDelete:Memo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Memo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Memo');
    }

    public function replicate(AuthUser $authUser, Memo $memo): bool
    {
        return $authUser->can('Replicate:Memo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Memo');
    }

}