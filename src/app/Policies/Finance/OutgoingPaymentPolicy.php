<?php

declare(strict_types=1);

namespace App\Policies\Finance;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Finance\OutgoingPayment;
use Illuminate\Auth\Access\HandlesAuthorization;

class OutgoingPaymentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OutgoingPayment');
    }

    public function view(AuthUser $authUser, OutgoingPayment $outgoingPayment): bool
    {
        return $authUser->can('View:OutgoingPayment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OutgoingPayment');
    }

    public function update(AuthUser $authUser, OutgoingPayment $outgoingPayment): bool
    {
        return $authUser->can('Update:OutgoingPayment');
    }

    public function delete(AuthUser $authUser, OutgoingPayment $outgoingPayment): bool
    {
        return $authUser->can('Delete:OutgoingPayment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OutgoingPayment');
    }

    public function restore(AuthUser $authUser, OutgoingPayment $outgoingPayment): bool
    {
        return $authUser->can('Restore:OutgoingPayment');
    }

    public function forceDelete(AuthUser $authUser, OutgoingPayment $outgoingPayment): bool
    {
        return $authUser->can('ForceDelete:OutgoingPayment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OutgoingPayment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OutgoingPayment');
    }

    public function replicate(AuthUser $authUser, OutgoingPayment $outgoingPayment): bool
    {
        return $authUser->can('Replicate:OutgoingPayment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OutgoingPayment');
    }

}