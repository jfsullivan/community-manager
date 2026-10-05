<?php

namespace jfsullivan\CommunityManager\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Community admins (the owner or admin-role members) manage the books;
 * members see their own transactions.
 */
class TransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny($user, $transactionOwner, $community)
    {
        if ($transactionOwner->id === $user->id) {
            return true;
        }

        return $community->isCommunityAdmin($user->id);
    }

    public function view($user, $transaction, $community)
    {
        if ($transaction->user_id === $user->id) {
            return true;
        }

        return $community->isCommunityAdmin($user->id);
    }

    public function create($user, $community)
    {
        return $community->isCommunityAdmin($user->id);
    }

    public function update($user, $community)
    {
        return $community->isCommunityAdmin($user->id);
    }

    public function delete($user, $community)
    {
        return $community->isCommunityAdmin($user->id);
    }

    public function deleteAny($user, $community)
    {
        return $community->isCommunityAdmin($user->id);
    }
}
