<?php

namespace jfsullivan\CommunityManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * `community-member`: the current community's pages are for its confirmed
 * members and its owner.
 *
 * - No current community: home.
 * - A pending or turned-down request (and `membership_status.enabled`):
 *   the membership-status page, which explains, instead of a bare 403.
 * - Anyone else who isn't a confirmed member or the owner: 403.
 *
 * Apps extend it and override shouldBypass() to let a request through
 * untouched (e.g. a site admin inspecting a disabled community).
 */
class EnsureIsCommunityMember
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        $communityClass = config('community-manager.community_model');

        $community = $communityClass::find($request->user()->current_community_id);

        if (! $community) {
            return redirect()->route('home');
        }

        $isMember = $request->user()->isConfirmedMember($community) || $request->user()->ownsCommunity($community);

        // A pending (start_at IS NULL) membership must NOT grant access — the
        // request is still awaiting admin confirmation (or was turned down).
        if (! $isMember && config('community-manager.membership_status.enabled', false) && $this->hasOpenOrTurnedDownRequest($request->user(), $community)) {
            return redirect()->route('community.membership-status', ['community' => $community->id]);
        }

        if (! $isMember) {
            abort(403, 'Unauthorized action');
        }

        $request->user()->communities()->updateExistingPivot(
            $request->user()->current_community_id,
            ['last_accessed_at' => now()]
        );

        return $next($request);
    }

    /** Whether to let the request through without any membership check. None by default. */
    protected function shouldBypass(Request $request): bool
    {
        return false;
    }

    /**
     * Whether the user asked to join and is still waiting, or was turned down
     * (a request ended without ever starting).
     */
    protected function hasOpenOrTurnedDownRequest($user, $community): bool
    {
        return $user->memberships()
            ->where('model_id', $community->id)
            ->where('model_type', $community->getMorphClass())
            ->whereNull('start_at')
            ->exists();
    }
}
