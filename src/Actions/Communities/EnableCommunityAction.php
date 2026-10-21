<?php

namespace jfsullivan\CommunityManager\Actions\Communities;

use Illuminate\Support\Facades\Notification;
use jfsullivan\CommunityManager\Events\CommunityEnabled as CommunityEnabledEvent;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Notifications\CommunityEnabled;
use jfsullivan\CommunityManager\Notifications\CommunityReenableRequested;
use jfsullivan\Notifications\Models\Notification as DatabaseNotification;

/**
 * Enables a disabled community again, clearing its reason and any re-enable
 * request or decline; answers a pending request (the site admins' notices of
 * it go away) and tells the community's owner and admins. Communities that
 * aren't disabled are left alone. Fires CommunityEnabled.
 *
 * Authorization is the caller's responsibility.
 */
class EnableCommunityAction
{
    public function enable(Community $community): Community
    {
        if (! $community->isDisabled()) {
            return $community;
        }

        $community->update([
            'disabled_at' => null,
            'disabled_reason' => null,
            'reenable_requested_at' => null,
            'reenable_request_message' => null,
            'reenable_declined_at' => null,
            'reenable_decline_message' => null,
        ]);

        DatabaseNotification::forSubject($community)
            ->where('type', config('community-manager.notifications.community_reenable_requested', CommunityReenableRequested::class))
            ->delete();

        $notification = config('community-manager.notifications.community_enabled', CommunityEnabled::class);
        Notification::send($community->administrators(), new $notification($community));

        CommunityEnabledEvent::dispatch($community);

        return $community;
    }
}
