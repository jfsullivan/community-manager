<?php

namespace jfsullivan\CommunityManager\Livewire\Articles;

use Illuminate\Support\Facades\Auth;
use jfsullivan\ArticleManager\Livewire\IndexPage as ArticleIndexPage;
use Livewire\Attributes\Computed;

class IndexPage extends ArticleIndexPage
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
    public function owningModel()
    {
        return $this->community;
    }

    #[Computed]
    public function layout()
    {
        return 'community-manager::components.layouts.community';
    }

    #[Computed]
    public function pageTitle()
    {
        return 'Community Articles';
    }

    public function render()
    {
        return view('article-manager::livewire.index-page')
            ->layout($this->layout, [
                'community' => $this->community,
                'selectedToolbarItem' => 'news',
            ])
            ->title($this->pageTitle);
    }
}
