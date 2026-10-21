<?php

namespace jfsullivan\CommunityManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use jfsullivan\CommunityManager\Models\Community;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locks a disabled community: every page inside it (routes matching
 * `disabling.community_routes`, except `disabling.open_routes`) is closed to
 * everyone but site admins. Its owner and admins are sent to the page saying
 * why and how to get it back (community.disabled); anyone else gets a short
 * "isn't available" page that gives no reason.
 *
 * Added to the web group (it filters by route name, so it covers the app's
 * community routes too) and as Livewire persistent middleware when
 * `community-manager.disabling.enabled` is on.
 */
class EnsureCommunityIsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null
            || ! $request->routeIs(config('community-manager.disabling.community_routes', ['community.*']))
            || $request->routeIs(config('community-manager.disabling.open_routes', ['community.disabled', 'community.membership-status']))) {
            return $next($request);
        }

        // A link naming its community (e.g. an article notification) is
        // checked against that community; every other page acts on the
        // user's current community.
        $communityClass = config('community-manager.community_model');
        $community = $request->route('community_id') !== null
            ? $communityClass::find((int) $request->route('community_id'))
            : $user->currentCommunity;

        if (! $community instanceof Community || ! $community->isLockedFor($user)) {
            return $next($request);
        }

        return $community->isRunBy($user)
            ? redirect()->route('community.disabled', ['community_id' => $community->id])
            : redirect()->route('unavailable', ['kind' => 'community']);
    }
}
