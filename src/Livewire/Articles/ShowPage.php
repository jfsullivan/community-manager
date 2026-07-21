<?php

namespace jfsullivan\CommunityManager\Livewire\Articles;

use jfsullivan\ArticleManager\Livewire\Pages\ArticleShowPage;
use jfsullivan\CommunityManager\Livewire\Concerns\ResolvesCommunity;

class ShowPage extends ArticleShowPage
{
    use ResolvesCommunity;

    public function layout(): string
    {
        return 'community-manager::components.layouts.community';
    }

    public function layoutProperties(): array
    {
        return [
            'community' => $this->community,
            'selectedToolbarItem' => 'news',
        ];
    }

    public function pageTitle(): string
    {
        return 'Community Articles - '.$this->article->title;
    }

    public function redirectOnDelete(): string
    {
        return route('community.articles.index');
    }
}
