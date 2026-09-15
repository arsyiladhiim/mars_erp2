<?php

declare(strict_types=1);

namespace App\Policies\Sales;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Sales\SalesQuotation;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesQuotationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SalesQuotation');
    }

    public function view(AuthUser $authUser, SalesQuotation $salesQuotation): bool
    {
        return $authUser->can('View:SalesQuotation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SalesQuotation');
    }

    public function update(AuthUser $authUser, SalesQuotation $salesQuotation): bool
    {
        return $authUser->can('Update:SalesQuotation');
    }

    public function delete(AuthUser $authUser, SalesQuotation $salesQuotation): bool
    {
        return $authUser->can('Delete:SalesQuotation');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SalesQuotation');
    }

    public function restore(AuthUser $authUser, SalesQuotation $salesQuotation): bool
    {
        return $authUser->can('Restore:SalesQuotation');
    }

    public function forceDelete(AuthUser $authUser, SalesQuotation $salesQuotation): bool
    {
        return $authUser->can('ForceDelete:SalesQuotation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SalesQuotation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SalesQuotation');
    }

    public function replicate(AuthUser $authUser, SalesQuotation $salesQuotation): bool
    {
        return $authUser->can('Replicate:SalesQuotation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SalesQuotation');
    }

}