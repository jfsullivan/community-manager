<?php

namespace jfsullivan\CommunityManager\Livewire\Articles\Pages;

use jfsullivan\ArticleManager\Livewire\Pages\ShowArticlePage as BaseShowArticlePage;
use jfsullivan\CommunityManager\Livewire\Concerns\ResolvesCommunity;

class ShowArticlePage extends BaseShowArticlePage
{
    use ResolvesCommunity;

    public function layout(): string
    {
        return config('community-manager.admin_layout');
    }

    public function layoutProperties(): array
    {
        return ['community' => $this->community];
    }
}
