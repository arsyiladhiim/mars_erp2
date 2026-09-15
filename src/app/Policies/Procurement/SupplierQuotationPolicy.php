<?php

declare(strict_types=1);

namespace App\Policies\Procurement;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Procurement\SupplierQuotation;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierQuotationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SupplierQuotation');
    }

    public function view(AuthUser $authUser, SupplierQuotation $supplierQuotation): bool
    {
        return $authUser->can('View:SupplierQuotation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SupplierQuotation');
    }

    public function update(AuthUser $authUser, SupplierQuotation $supplierQuotation): bool
    {
        return $authUser->can('Update:SupplierQuotation');
    }

    public function delete(AuthUser $authUser, SupplierQuotation $supplierQuotation): bool
    {
        return $authUser->can('Delete:SupplierQuotation');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SupplierQuotation');
    }

    public function restore(AuthUser $authUser, SupplierQuotation $supplierQuotation): bool
    {
        return $authUser->can('Restore:SupplierQuotation');
    }

    public function forceDelete(AuthUser $authUser, SupplierQuotation $supplierQuotation): bool
    {
        return $authUser->can('ForceDelete:SupplierQuotation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SupplierQuotation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SupplierQuotation');
    }

    public function replicate(AuthUser $authUser, SupplierQuotation $supplierQuotation): bool
    {
        return $authUser->can('Replicate:SupplierQuotation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SupplierQuotation');
    }

}