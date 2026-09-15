<?php

declare(strict_types=1);

namespace App\Policies\Inventory;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Inventory\StockLedgerEntry;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockLedgerEntryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StockLedgerEntry');
    }

    public function view(AuthUser $authUser, StockLedgerEntry $stockLedgerEntry): bool
    {
        return $authUser->can('View:StockLedgerEntry');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StockLedgerEntry');
    }

    public function update(AuthUser $authUser, StockLedgerEntry $stockLedgerEntry): bool
    {
        return $authUser->can('Update:StockLedgerEntry');
    }

    public function delete(AuthUser $authUser, StockLedgerEntry $stockLedgerEntry): bool
    {
        return $authUser->can('Delete:StockLedgerEntry');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StockLedgerEntry');
    }

    public function restore(AuthUser $authUser, StockLedgerEntry $stockLedgerEntry): bool
    {
        return $authUser->can('Restore:StockLedgerEntry');
    }

    public function forceDelete(AuthUser $authUser, StockLedgerEntry $stockLedgerEntry): bool
    {
        return $authUser->can('ForceDelete:StockLedgerEntry');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StockLedgerEntry');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StockLedgerEntry');
    }

    public function replicate(AuthUser $authUser, StockLedgerEntry $stockLedgerEntry): bool
    {
        return $authUser->can('Replicate:StockLedgerEntry');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StockLedgerEntry');
    }

}