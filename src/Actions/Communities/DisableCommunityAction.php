<?php

namespace jfsullivan\CommunityManager\Actions\Communities;

use Illuminate\Support\Facades\Notification;
use jfsullivan\CommunityManager\Events\CommunityDisabled as CommunityDisabledEvent;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Notifications\CommunityDisabled;

/**
 * Disables a shared community (e.g. a terms violation) with the reason its
 * owner and admins are shown, clearing any earlier re-enable request or
 * decline, and tells them. The community turns read-only; nothing else is
 * written, so enabling it restores everything. Personal and already-disabled
 * communities are left alone. Fires CommunityDisabled for app side effects.
 *
 * Authorization is the caller's responsibility.
 */
class DisableCommunityAction
{
    public function disable(Community $community, string $reason): Community
    {
        if ($community->isPersonal() || $community->isDisabled()) {
            return $community;
        }

        $community->update([
            'disabled_at' => now(),
            'disabled_reason' => trim($reason),
            'reenable_requested_at' => null,
            'reenable_request_message' => null,
            'reenable_declined_at' => null,
            'reenable_decline_message' => null,
        ]);

        $notification = config('community-manager.notifications.community_disabled', CommunityDisabled::class);
        Notification::send($community->administrators(), new $notification($community));

        CommunityDisabledEvent::dispatch($community);

        return $community;
    }
}
