<?php

namespace jfsullivan\CommunityManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use jfsullivan\CommunityManager\Models\Community;
use Symfony\Component\HttpFoundation\Response;

/**
 * A disabled community is closed to everyone but site admins
 * (EnsureCommunityIsEnabled); this keeps it read-only for them too: its
 * pages that only add or send something (`disabling.write_only_routes`)
 * send them back to the community admin dashboard with a warning. The
 * individual write paths are refused in their own policies and components;
 * this just stops direct navigation.
 *
 * Added to the web group and as Livewire persistent middleware when
 * `community-manager.disabling.enabled` is on.
 */
class EnsureCommunityIsWritable
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->routeIs(config('community-manager.disabling.write_only_routes', []))) {
            return $next($request);
        }

        $community = $request->user()?->currentCommunity;

        if ($community instanceof Community && $community->isDisabled()) {
            return redirect()->route('community.admin.index')
                ->with('warning', 'This community has been disabled by '.config('app.name').', so nothing new can be added or sent.');
        }

        return $next($request);
    }
}
