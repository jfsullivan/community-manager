<?php

namespace jfsullivan\CommunityManager\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class CommunityArticlePolicy
{
    use HandlesAuthorization;

    public function viewAny($user)
    {
        return true;
    }

    public function view($user, $article, $community)
    {
        return $user->belongsToCommunity($community);
    }

    public function create($user, $community)
    {
        return $user->ownsCommunity($community);
    }

    public function update($user, $article, $community)
    {
        return $user->ownsCommunity($community);
    }

    public function delete($user, $article, $community)
    {
        return $user->ownsCommunity($community);
    }

    public function publish($user, $article, $community)
    {
        return $user->ownsCommunity($community);
    }

    public function unpublish($user, $article, $community)
    {
        return $user->ownsCommunity($community);
    }

    public function send($user, $article, $community)
    {
        return $user->ownsCommunity($community);
    }
}
