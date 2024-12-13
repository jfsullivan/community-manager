<?php

namespace jfsullivan\CommunityManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use jfsullivan\CommunityManager\Models\Community;

class CommunityController extends Controller
{
    public function show(Request $request, Community $community): View
    {
        if (Gate::denies('view', $community)) {
            abort(403);
        }

        return view('community-manager::show', [
            'user' => $request->user(),
            'community' => $community,
        ]);
    }

    public function dashboard(Request $request): View
    {
        $community = $request->user()->currentCommunity;

        if (Gate::denies('view', $community)) {
            abort(403);
        }

        return view('community-manager::dashboard', [
            'user' => $request->user(),
            'community' => $community,
        ]);
    }
}
