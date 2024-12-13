<?php

namespace jfsullivan\CommunityManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsCommunityMember
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $communityClass = config('community-manager.community_model');

        $community = $communityClass::find($request->user()->current_community_id);

        if (! $community) {
            return redirect()->route('home');
        }

        if (! $request->user()->isMember($community) && ! $request->user()->ownsCommunity($community)) {
            abort(403, 'Unauthorized action');
        }

        $request->user()->communities()->updateExistingPivot(
            $request->user()->current_community_id,
            ['last_accessed_at' => now()]
        );

        return $next($request);
    }
}
