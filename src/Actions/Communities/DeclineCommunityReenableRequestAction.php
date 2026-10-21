<?php

namespace jfsullivan\CommunityManager\Actions\Communities;

use Illuminate\Support\Facades\Notification;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Notifications\CommunityReenableDeclined;
use jfsullivan\CommunityManager\Notifications\CommunityReenableRequested;
use jfsullivan\Notifications\Models\Notification as DatabaseNotification;

/**
 * Turns down a pending request to enable a disabled community again: it stays
 * disabled, the site admin's message is kept for its owner and admins (who
 * can answer with a new request), the site admins' notices of the request go
 * away, and the community's owner and admins are told.
 *
 * Authorization is the caller's responsibility.
 */
class DeclineCommunityReenableRequestAction
{
    public function decline(Community $community, string $message): Community
    {
        if (! $community->isDisabled() || $community->reenable_requested_at === null) {
            return $community;
        }

        $community->update([
            'reenable_requested_at' => null,
            'reenable_request_message' => null,
            'reenable_declined_at' => now(),
            'reenable_decline_message' => trim($message),
        ]);

        DatabaseNotification::forSubject($community)
            ->where('type', config('community-manager.notifications.community_reenable_requested', CommunityReenableRequested::class))
            ->delete();

        $notification = config('community-manager.notifications.community_reenable_declined', CommunityReenableDeclined::class);
        Notification::send($community->administrators(), new $notification($community));

        return $community;
    }
}
