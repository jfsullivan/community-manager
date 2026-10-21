<?php

namespace jfsullivan\CommunityManager\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Notification;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Notifications\MembershipRejected;

/**
 * Turn down a pending request to join a community. The membership row is kept
 * as a tombstone (status removed / rejected-by-admin, end_at set), so a later
 * app-driven join doesn't quietly ask again, and the person is told through
 * config('community-manager.notifications.membership_rejected'). Callers
 * authorize first.
 */
class RejectMembership
{
    public function handle(Community $community, Authenticatable $user): void
    {
        $userId = $user->getAuthIdentifier();

        $community->memberships()
            ->where('memberships.user_id', $userId)
            ->first()
            ?->setStatus('removed', 'rejected-by-admin');

        $community->members()->sync([$userId => ['end_at' => now()]], false);

        $notification = config('community-manager.notifications.membership_rejected', MembershipRejected::class);

        Notification::send($user, new $notification($community));
    }
}
