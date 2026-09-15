<?php

declare(strict_types=1);

namespace App\Policies\Finance;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Finance\CashBankTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class CashBankTransactionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CashBankTransaction');
    }

    public function view(AuthUser $authUser, CashBankTransaction $cashBankTransaction): bool
    {
        return $authUser->can('View:CashBankTransaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CashBankTransaction');
    }

    public function update(AuthUser $authUser, CashBankTransaction $cashBankTransaction): bool
    {
        return $authUser->can('Update:CashBankTransaction');
    }

    public function delete(AuthUser $authUser, CashBankTransaction $cashBankTransaction): bool
    {
        return $authUser->can('Delete:CashBankTransaction');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CashBankTransaction');
    }

    public function restore(AuthUser $authUser, CashBankTransaction $cashBankTransaction): bool
    {
        return $authUser->can('Restore:CashBankTransaction');
    }

    public function forceDelete(AuthUser $authUser, CashBankTransaction $cashBankTransaction): bool
    {
        return $authUser->can('ForceDelete:CashBankTransaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CashBankTransaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CashBankTransaction');
    }

    public function replicate(AuthUser $authUser, CashBankTransaction $cashBankTransaction): bool
    {
        return $authUser->can('Replicate:CashBankTransaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CashBankTransaction');
    }

}