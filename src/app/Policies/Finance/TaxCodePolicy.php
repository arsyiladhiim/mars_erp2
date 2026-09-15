<?php

declare(strict_types=1);

namespace App\Policies\Finance;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Finance\TaxCode;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaxCodePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TaxCode');
    }

    public function view(AuthUser $authUser, TaxCode $taxCode): bool
    {
        return $authUser->can('View:TaxCode');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TaxCode');
    }

    public function update(AuthUser $authUser, TaxCode $taxCode): bool
    {
        return $authUser->can('Update:TaxCode');
    }

    public function delete(AuthUser $authUser, TaxCode $taxCode): bool
    {
        return $authUser->can('Delete:TaxCode');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TaxCode');
    }

    public function restore(AuthUser $authUser, TaxCode $taxCode): bool
    {
        return $authUser->can('Restore:TaxCode');
    }

    public function forceDelete(AuthUser $authUser, TaxCode $taxCode): bool
    {
        return $authUser->can('ForceDelete:TaxCode');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TaxCode');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TaxCode');
    }

    public function replicate(AuthUser $authUser, TaxCode $taxCode): bool
    {
        return $authUser->can('Replicate:TaxCode');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TaxCode');
    }

}