<?php

namespace jfsullivan\CommunityManager\Livewire\Articles\Pages;

use jfsullivan\ArticleManager\Livewire\Pages\CreateArticlePage as BaseCreateArticlePage;
use jfsullivan\CommunityManager\Livewire\Concerns\ResolvesCommunity;

class CreateArticlePage extends BaseCreateArticlePage
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
