<?php

namespace jfsullivan\CommunityManager\Livewire\Articles\Pages;

use jfsullivan\ArticleManager\Livewire\Pages\ArticleManagementPage as BaseArticleManagementPage;
use jfsullivan\CommunityManager\Livewire\Concerns\ResolvesCommunity;

class ArticleManagementPage extends BaseArticleManagementPage
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
