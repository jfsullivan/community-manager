<?php

namespace jfsullivan\CommunityManager\Actions\Communities;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Notification;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Notifications\CommunityReenableRequested;
use jfsullivan\Notifications\Models\Notification as DatabaseNotification;

/**
 * Records a request, from the owner or an admin of a disabled community, to
 * have it enabled again: it replaces any earlier request and clears an
 * earlier decline, and every site admin (users with
 * config('community-manager.site_admin_column') set) is told; their notices
 * of an earlier request are replaced, so each holds one per community.
 *
 * Authorization is the caller's responsibility.
 */
class RequestCommunityReenableAction
{
    public function request(Community $community, Authenticatable $requester, string $message): Community
    {
        $community->update([
            'reenable_requested_at' => now(),
            'reenable_request_message' => trim($message),
            'reenable_declined_at' => null,
            'reenable_decline_message' => null,
        ]);

        $notification = config('community-manager.notifications.community_reenable_requested', CommunityReenableRequested::class);

        DatabaseNotification::forSubject($community)->where('type', $notification)->delete();

        $siteAdmins = config('community-manager.user_model')::query()
            ->where(config('community-manager.site_admin_column', 'is_site_admin'), true)
            ->get();

        Notification::send($siteAdmins, new $notification($community, $requester));

        return $community;
    }
}
