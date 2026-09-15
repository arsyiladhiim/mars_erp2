<?php

declare(strict_types=1);

namespace App\Policies\Inventory;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Inventory\BatchSerial;
use Illuminate\Auth\Access\HandlesAuthorization;

class BatchSerialPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BatchSerial');
    }

    public function view(AuthUser $authUser, BatchSerial $batchSerial): bool
    {
        return $authUser->can('View:BatchSerial');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BatchSerial');
    }

    public function update(AuthUser $authUser, BatchSerial $batchSerial): bool
    {
        return $authUser->can('Update:BatchSerial');
    }

    public function delete(AuthUser $authUser, BatchSerial $batchSerial): bool
    {
        return $authUser->can('Delete:BatchSerial');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BatchSerial');
    }

    public function restore(AuthUser $authUser, BatchSerial $batchSerial): bool
    {
        return $authUser->can('Restore:BatchSerial');
    }

    public function forceDelete(AuthUser $authUser, BatchSerial $batchSerial): bool
    {
        return $authUser->can('ForceDelete:BatchSerial');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BatchSerial');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BatchSerial');
    }

    public function replicate(AuthUser $authUser, BatchSerial $batchSerial): bool
    {
        return $authUser->can('Replicate:BatchSerial');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BatchSerial');
    }

}