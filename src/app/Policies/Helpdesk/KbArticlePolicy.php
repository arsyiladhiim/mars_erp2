<?php

declare(strict_types=1);

namespace App\Policies\Helpdesk;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Helpdesk\KbArticle;
use Illuminate\Auth\Access\HandlesAuthorization;

class KbArticlePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KbArticle');
    }

    public function view(AuthUser $authUser, KbArticle $kbArticle): bool
    {
        return $authUser->can('View:KbArticle');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KbArticle');
    }

    public function update(AuthUser $authUser, KbArticle $kbArticle): bool
    {
        return $authUser->can('Update:KbArticle');
    }

    public function delete(AuthUser $authUser, KbArticle $kbArticle): bool
    {
        return $authUser->can('Delete:KbArticle');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KbArticle');
    }

    public function restore(AuthUser $authUser, KbArticle $kbArticle): bool
    {
        return $authUser->can('Restore:KbArticle');
    }

    public function forceDelete(AuthUser $authUser, KbArticle $kbArticle): bool
    {
        return $authUser->can('ForceDelete:KbArticle');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KbArticle');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KbArticle');
    }

    public function replicate(AuthUser $authUser, KbArticle $kbArticle): bool
    {
        return $authUser->can('Replicate:KbArticle');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KbArticle');
    }

}