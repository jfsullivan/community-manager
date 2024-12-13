<?php

namespace jfsullivan\CommunityManager\Http\Controllers;

use Illuminate\Http\Request;
use jfsullivan\CommunityManager\Http\Controllers\Controller;
use jfsullivan\CommunityManager\Models\Community;

class CurrentCommunityController extends Controller
{
    /**
     * Update the authenticated user's current community.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $communityClass = config('community-manager.community_model');

        $community = $communityClass::findOrFail($request->community_id);

        if (! $request->user()->switchCommunity($community)) {
            abort(403);
        }

        return redirect(route('community.dashboard'), 303);
    }
}
