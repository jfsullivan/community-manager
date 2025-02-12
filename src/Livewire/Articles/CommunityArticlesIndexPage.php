<?php

namespace jfsullivan\CommunityManager\Livewire\Articles;

use Illuminate\Database\Eloquent\Relations\Relation;
use jfsullivan\ArticleManager\Livewire\IndexPage as ArticleIndexPage;
use Livewire\Attribute\Computed;

class CommunityArticlesIndexPage extends ArticleIndexPage
{
    // public function mount(string $model_type, int $model_id)
    // {
    //     $class = Relation::getMorphedModel($model_type);
    //     $this->model = $class::find($model_id);

    //     $this->perPage = 10;
    // }

    #[Computed]
    public function community()
    {
        return $this->user->currentCommunity;
    }

    public function render()
    {
        dd('test');

        return view('community-manager::livewire.articles.index', [
            'articles' => $this->records,
            'community' => $this->community,
        ]);
    }
}
