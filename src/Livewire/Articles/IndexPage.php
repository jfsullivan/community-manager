<?php

namespace jfsullivan\CommunityManager\Livewire\Articles;

use jfsullivan\ArticleManager\Livewire\Pages\ArticleIndexPage;
use jfsullivan\CommunityManager\Livewire\Concerns\ResolvesCommunity;

class IndexPage extends ArticleIndexPage
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

    public function view(): string
    {
        return 'community-manager::livewire.articles.index-page';
    }

    public function pageTitle(): string
    {
        return 'Community Articles';
    }
}
