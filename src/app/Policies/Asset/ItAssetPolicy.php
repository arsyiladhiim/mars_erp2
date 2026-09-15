<?php

declare(strict_types=1);

namespace App\Policies\Asset;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Asset\ItAsset;
use Illuminate\Auth\Access\HandlesAuthorization;

class ItAssetPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ItAsset');
    }

    public function view(AuthUser $authUser, ItAsset $itAsset): bool
    {
        return $authUser->can('View:ItAsset');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ItAsset');
    }

    public function update(AuthUser $authUser, ItAsset $itAsset): bool
    {
        return $authUser->can('Update:ItAsset');
    }

    public function delete(AuthUser $authUser, ItAsset $itAsset): bool
    {
        return $authUser->can('Delete:ItAsset');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ItAsset');
    }

    public function restore(AuthUser $authUser, ItAsset $itAsset): bool
    {
        return $authUser->can('Restore:ItAsset');
    }

    public function forceDelete(AuthUser $authUser, ItAsset $itAsset): bool
    {
        return $authUser->can('ForceDelete:ItAsset');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ItAsset');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ItAsset');
    }

    public function replicate(AuthUser $authUser, ItAsset $itAsset): bool
    {
        return $authUser->can('Replicate:ItAsset');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ItAsset');
    }

}