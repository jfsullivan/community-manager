<?php

namespace jfsullivan\CommunityManager\Livewire\Articles;

use Illuminate\Support\Facades\Auth;
use jfsullivan\ArticleManager\Livewire\ShowPage as ArticleShowPage;
use Livewire\Attributes\Computed;
use Illuminate\Support\Str;

class ShowPage extends ArticleShowPage
{
    public $community_id;

    #[Computed]
    public function community()
    {
        $communityClass = app(config('community-manager.community_model'));

        return ($this->community_id)
            ? $communityClass::find($this->community_id)
            : Auth::user()->currentCommunity;
    }

    #[Computed]
    public function redirectOnDelete(): string
    {
        return route('community.articles.index');
    }

    #[Computed]
    public function owningModel()
    {
        return $this->community;
    }

    #[Computed]
    public function layout(): string
    {
        return 'community-manager::components.layouts.community';
    }

    #[Computed]
    public function pageTitle(): string
    {
        return 'Community Articles - ' . $this->article->title;
    }

    public function render()
    {
        return view('article-manager::livewire.show-page')
            ->layout($this->layout, [
                'community' => $this->community,
                'selectedToolbarItem' => 'news'
            ])
            ->title($this->pageTitle);
    }
}
