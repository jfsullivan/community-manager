<?php

namespace jfsullivan\CommunityManager\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny($user, $transactionOwner, $community)
    {
        if($transactionOwner->id === $user->id) {
            return true;
        }

        return $user->ownsCommunity($community);
    }

    public function view($user, $transaction, $community)
    {
        if($transaction->user_id === $user->id) {
            return true;
        }

        return $user->ownsCommunity($community);
    }

    public function create($user, $community)
    {
        return $user->ownsCommunity($community);
    }

    public function update($user, $community)
    {
        return $user->ownsCommunity($community);
    }

    public function delete($user, $community)
    {
        return $user->ownsCommunity($community);
    }

    public function deleteAny($user, $community)
    {
        return $user->ownsCommunity($community);
    }
}
