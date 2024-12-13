<?php

namespace jfsullivan\CommunityManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use jfsullivan\ArticleManager\Models\Article;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $community = $request->user()->currentCommunity;

        // if (Gate::denies('view', $community)) {
        //     abort(403);
        // }

        return view('community-manager::articles.index', [
            'user' => $request->user(),
            'community' => $community,
        ]);
    }

    public function show(Request $request, $id): View
    {
        $community = $request->user()->currentCommunity;
        dd('test');
        $article = Article::findOrFail($id);

        // if (Gate::denies('view', $community)) {
        //     abort(403);
        // }

        return view('community-manager::articles.show', [
            'user' => $request->user(),
            'community' => $community,
            'article' => $article,
        ]);
    }
}
