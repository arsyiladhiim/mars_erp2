<?php

declare(strict_types=1);

namespace App\Policies\Ai;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Ai\AiProvider;
use Illuminate\Auth\Access\HandlesAuthorization;

class AiProviderPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AiProvider');
    }

    public function view(AuthUser $authUser, AiProvider $aiProvider): bool
    {
        return $authUser->can('View:AiProvider');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AiProvider');
    }

    public function update(AuthUser $authUser, AiProvider $aiProvider): bool
    {
        return $authUser->can('Update:AiProvider');
    }

    public function delete(AuthUser $authUser, AiProvider $aiProvider): bool
    {
        return $authUser->can('Delete:AiProvider');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AiProvider');
    }

    public function restore(AuthUser $authUser, AiProvider $aiProvider): bool
    {
        return $authUser->can('Restore:AiProvider');
    }

    public function forceDelete(AuthUser $authUser, AiProvider $aiProvider): bool
    {
        return $authUser->can('ForceDelete:AiProvider');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AiProvider');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AiProvider');
    }

    public function replicate(AuthUser $authUser, AiProvider $aiProvider): bool
    {
        return $authUser->can('Replicate:AiProvider');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AiProvider');
    }

}