<?php

namespace jfsullivan\CommunityManager\Livewire\Articles;

use App\Models\Pool;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use jfsullivan\ArticleManager\Livewire\CreatePage as ArticleCreatePage;

class CreatePage extends ArticleCreatePage
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
    public function layout() : string
    {
        return 'community-manager::components.layouts.community';
    }

    #[Computed]
    public function pageTitle() : string
    {
        return 'New Community Article';
    }

    public function render()
    {
        return view('article-manager::livewire.article-form')
            ->title($this->pageTitle)
            ->layout($this->layout, [
                // 'pool' => $this->pool,
                'selectedToolbarItem' => 'news'
            ]);
    }
}
