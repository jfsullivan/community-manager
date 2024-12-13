<?php

namespace jfsullivan\CommunityManager\Livewire\Articles;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Component;
use jfsullivan\ArticleManager\Livewire\Show as ArticleShow;

class Show extends ArticleShow
{

    public function mount(string $model_type = null, int $id, string $redirect = null)
    {
        $this->model_type = 'community';
        $this->model = $this->user->currentCommunity;

        $this->article_id = $id;
    }

    public function getCommunityProperty()
    {
        return $this->user->currentCommunity;
    }

    public function getArticleProperty()
    {
        return $this->model->articles()->findOrFail($this->article_id);
    }

    public function render()
    {
        return view('community-manager::livewire.articles.show', [
            'article' => $this->article,
            'community' => $this->community,
        ]);
    }
}
