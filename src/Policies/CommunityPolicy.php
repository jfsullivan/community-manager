<?php

namespace jfsullivan\CommunityManager\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User as Authenticatable;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Traits\ChecksForFeatures;

/**
 * Community admins (the owner, or a current member holding the `admin` role)
 * run the community: settings, members, invitations, pools and accounting.
 * What stays with the owner alone:
 *
 * - the admin role: granting it, and changing or removing an existing admin
 *   (so admins can't promote others or demote each other);
 * - deleting the community.
 *
 * Paying for the community (its subscription) is the owner's too; the host
 * app gates that on ownership directly.
 */
class CommunityPolicy
{
    use ChecksForFeatures;
    use HandlesAuthorization;

    /** Role slugs only the owner may grant, change or remove. */
    public const OWNER_MANAGED_ROLES = ['owner', 'admin'];

    public function viewAny($user)
    {
        return true;
    }

    public function view(Authenticatable $user, Community $community)
    {
        // Confirmed members only — a pending (start_at IS NULL) row does not
        // grant view access until an admin confirms it.
        return $community->isOwner($user->id) || $community->hasConfirmedMember($user->id);
    }

    public function create(Authenticatable $user)
    {
        return false; // TODO: Implement logic to create() and charge for communities.
    }

    public function update(Authenticatable $user, Community $community)
    {
        return $community->isCommunityAdmin($user->id);
    }

    public function renameCommunity(Authenticatable $user, Community $community)
    {
        return $community->isCommunityAdmin($user->id);
    }

    public function addCommunityMember(Authenticatable $user, Community $community)
    {
        return $community->isCommunityAdmin($user->id);
    }

    public function inviteCommunityMember($user, Community $community)
    {
        return $community->isCommunityAdmin($user->id);
    }

    public function updateCommunityMember($user, Community $community)
    {
        return $community->isCommunityAdmin($user->id);
    }

    public function removeCommunityMember($user, Community $community)
    {
        return $community->isCommunityAdmin($user->id);
    }

    /** member-manager: may the user manage this community's members at all? */
    public function manageMembers($user, Community $community)
    {
        return $community->isCommunityAdmin($user->id);
    }

    /**
     * member-manager: may the user give a member this role? The owner and
     * admin roles are the owner's to hand out.
     */
    public function assignMemberRole($user, Community $community, $role)
    {
        if (! $community->isCommunityAdmin($user->id)) {
            return false;
        }

        return $community->isOwner($user->id) || ! in_array($role?->slug, self::OWNER_MANAGED_ROLES, true);
    }

    /**
     * member-manager: may the user change or remove this particular member?
     * Admins manage everyone but the owner and other admins; the owner manages
     * everyone but themselves.
     */
    public function manageMember($user, Community $community, $membership): Response
    {
        if (! $community->isCommunityAdmin($user->id)) {
            return Response::deny('Only community admins can manage members.');
        }

        if ($community->isOwner($membership->user_id)) {
            return Response::deny('The community owner\'s membership can\'t be changed here.');
        }

        if (! $community->isOwner($user->id) && in_array($membership->role?->slug, self::OWNER_MANAGED_ROLES, true)) {
            return Response::deny('Only the community owner can change or remove another admin.');
        }

        return Response::allow();
    }

    public function delete($user, Community $community)
    {
        return $community->isOwner($user->id);
    }

    public function manage($user, Community $community)
    {
        return $community->isCommunityAdmin($user->id);
    }

    public function viewMemberBalance($user, Community $community)
    {
        return config('community-manager.features.track_member_balances') && $community->track_member_balances;
    }

    public function addFunds($user, Community $community)
    {
        return $community->isCommunityAdmin($user->id)
            || (config('community-manager.features.track_member_balances') && $community->track_member_balances);
    }
}
