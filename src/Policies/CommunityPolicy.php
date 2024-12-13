<?php

namespace jfsullivan\CommunityManager\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Traits\ChecksForFeatures;

class CommunityPolicy
{
    use ChecksForFeatures;
    use HandlesAuthorization;

    public function before(Authenticatable $user, string $ability): ?bool
    {
        if ($user->ownsCommunity(auth()->user()->currentCommunity)) {
            return true;
        }

        return null;
    }

    public function viewAny($user)
    {
        return true;
    }

    public function view(Authenticatable $user, Community $community)
    {
        return $community->hasMember($user->id);
    }

    public function create(Authenticatable $user)
    {
        return false; // TODO: Implement logic to create() and charge for communities.
    }

    public function update(Authenticatable $user, Community $community)
    {
        return $community->isOwner($user->id);
    }

    public function addCommunityMember(Authenticatable $user, Community $community)
    {
        return $community->isOwner($user->id);
    }

    public function inviteCommunityMember($user, Community $community)
    {
        return $community->isOwner($user->id);
    }

    public function updateCommunityMember($user, Community $community)
    {
        return $community->isOwner($user->id);
    }

    public function removeCommunityMember($user, Community $community)
    {
        return $community->isOwner($user->id);
    }

    public function delete($user, Community $community)
    {
        return $community->isOwner($user->id);
    }

    public function manage($user, Community $community)
    {
        return $this->create($user, $community)
                || $this->update($user, $community)
                || $this->delete($user, $community);
    }

    public function viewMemberBalance($user, Community $community)
    {
        return config('community-manager.features.track_member_balances') && $community->track_member_balances;
    }

    public function addFunds($user, Community $community)
    {
        return $community->isOwner($user->id)
            || (config('community-manager.features.track_member_balances') && $community->track_member_balances);
    }
}
