<?php

declare(strict_types=1);

namespace App\Policies\Core;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Core\SystemPreference;
use Illuminate\Auth\Access\HandlesAuthorization;

class SystemPreferencePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SystemPreference');
    }

    public function view(AuthUser $authUser, SystemPreference $systemPreference): bool
    {
        return $authUser->can('View:SystemPreference');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SystemPreference');
    }

    public function update(AuthUser $authUser, SystemPreference $systemPreference): bool
    {
        return $authUser->can('Update:SystemPreference');
    }

    public function delete(AuthUser $authUser, SystemPreference $systemPreference): bool
    {
        return $authUser->can('Delete:SystemPreference');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SystemPreference');
    }

    public function restore(AuthUser $authUser, SystemPreference $systemPreference): bool
    {
        return $authUser->can('Restore:SystemPreference');
    }

    public function forceDelete(AuthUser $authUser, SystemPreference $systemPreference): bool
    {
        return $authUser->can('ForceDelete:SystemPreference');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SystemPreference');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SystemPreference');
    }

    public function replicate(AuthUser $authUser, SystemPreference $systemPreference): bool
    {
        return $authUser->can('Replicate:SystemPreference');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SystemPreference');
    }

}