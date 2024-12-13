<?php

namespace jfsullivan\CommunityManager\Livewire\Articles\Pages;

use Illuminate\Support\Facades\Auth;
use jfsullivan\ArticleManager\Livewire\Pages\CreateArticlePage as BaseCreateArticlePage;
use Livewire\Attributes\Computed;

class CreateArticlePage extends BaseCreateArticlePage
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
    public function owningModel(): mixed
    {
        return $this->community;
    }

    public function layout(): string
    {
        return config('community-manager.admin_layout');
    }

    public function render()
    {
        return view('article-manager::livewire.pages.create-article-page')
            ->layout($this->layout(), [
                'community' => $this->community,
            ]);
    }
}
