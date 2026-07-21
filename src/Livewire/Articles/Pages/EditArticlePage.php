<?php

namespace jfsullivan\CommunityManager\Livewire\Articles\Pages;

use jfsullivan\ArticleManager\Livewire\Pages\EditArticlePage as BaseEditArticlePage;
use jfsullivan\CommunityManager\Livewire\Concerns\ResolvesCommunity;

class EditArticlePage extends BaseEditArticlePage
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
