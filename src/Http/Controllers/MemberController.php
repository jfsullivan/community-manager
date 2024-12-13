<?php

namespace jfsullivan\CommunityManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $community = $request->user()->currentCommunity;

        return view('community-manager::membership.members', compact('community'));
    }

    public function manage(Request $request)
    {
        $community = $request->user()->currentCommunity;

        return view('community-manager::membership.member-manager', compact('community'));
    }
}
