<?php

namespace jfsullivan\CommunityManager\Livewire\Articles;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use JamesMills\LaravelTimezone\Facades\Timezone;
use jfsullivan\ArticleManager\Livewire\Forms\ArticleForm;
use jfsullivan\ArticleManager\Livewire\Traits\HasAttachments;
use jfsullivan\ArticleManager\Livewire\Traits\UsesRouteDetails;
use jfsullivan\ArticleManager\Models\Article;
use jfsullivan\ArticleManager\Notifications\ArticlePublished;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditPage extends Component
{
    use HasAttachments;
    use UsesRouteDetails;
    use WithFileUploads;

    public $community_id;

    public ArticleForm $form;

    public Article $article;

    public $action = 'edit';

    public $allow_attachments = false;

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
    public function layout(): string
    {
        return 'community-manager::components.layouts.community';
    }

    #[Computed]
    public function pageTitle(): string
    {
        return 'Community Articles - Edit Article';
    }

    public function mount($owningModel)
    {
        $this->form->setModel($this->owningModel);
        $this->form->setArticle($this->article);
    }

    public function setAttachmentRequirement()
    {
        $this->allowableAttachmentTypes = 'pdf,xls,xlsx,doc,docx,ppt,pptx,txt,zip';
        $this->maxAttachmentSize = '20000';
    }

    public function returnRoute()
    {
        return route($this->baseRouteName.'.articles.show', collect($this->routeParameters)->merge(['article' => $this->article->id])->toArray());
    }

    public function save()
    {
        if (! Gate::allows('update-'.$this->owningModelAlias.'-article', [$this->article, $this->owningModel])) {
            $this->dispatch('notify',
                type: 'error',
                title: 'Unauthorized',
                message: 'You don\'t have the proper permission to update this article.',
            );

            return;
        }

        $this->article = $this->form->update();

        $this->dispatch('article-updated');
        $this->dispatch('refresh-article-list');

        session()->flash('notify', [
            'type' => 'success',
            'title' => 'Success',
            'message' => 'Your article has been updated.',
        ]);

        $this->redirect($this->returnRoute());
    }

    public function publishNow()
    {
        if (! Gate::allows('update-'.$this->owningModelAlias.'-article', [$this->owningModel])) {
            $this->dispatch('notify',
                type: 'error',
                title: 'Unauthorized',
                message: 'You don\'t have the proper permission to update this article.',
            );

            return;
        }

        $this->validate();

        $this->form->start_at = Timezone::convertToLocal(Carbon::now(), 'Y-m-d H:i:s');

        $this->article = $this->form->update();

        $this->dispatch('article-updated');
        $this->dispatch('article-published');
        $this->dispatch('refresh-article-list');

        session()->flash('notify', [
            'type' => 'success',
            'title' => 'Success',
            'message' => 'Your article has been updated and published.',
        ]);

        if ($this->article->isPublished()) {
            Notification::send($this->model->members, new ArticlePublished($this->article));
        }

        $this->redirect($this->returnRoute());
    }

    public function render()
    {
        return view('article-manager::livewire.form')
            ->layout($this->layout, [
                'community' => $this->community,
                'selectedToolbarItem' => 'news',
            ])
            ->title($this->pageTitle);
    }
}
