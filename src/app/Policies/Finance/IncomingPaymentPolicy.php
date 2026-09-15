<?php

declare(strict_types=1);

namespace App\Policies\Finance;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Finance\IncomingPayment;
use Illuminate\Auth\Access\HandlesAuthorization;

class IncomingPaymentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:IncomingPayment');
    }

    public function view(AuthUser $authUser, IncomingPayment $incomingPayment): bool
    {
        return $authUser->can('View:IncomingPayment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:IncomingPayment');
    }

    public function update(AuthUser $authUser, IncomingPayment $incomingPayment): bool
    {
        return $authUser->can('Update:IncomingPayment');
    }

    public function delete(AuthUser $authUser, IncomingPayment $incomingPayment): bool
    {
        return $authUser->can('Delete:IncomingPayment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:IncomingPayment');
    }

    public function restore(AuthUser $authUser, IncomingPayment $incomingPayment): bool
    {
        return $authUser->can('Restore:IncomingPayment');
    }

    public function forceDelete(AuthUser $authUser, IncomingPayment $incomingPayment): bool
    {
        return $authUser->can('ForceDelete:IncomingPayment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:IncomingPayment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:IncomingPayment');
    }

    public function replicate(AuthUser $authUser, IncomingPayment $incomingPayment): bool
    {
        return $authUser->can('Replicate:IncomingPayment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:IncomingPayment');
    }

}