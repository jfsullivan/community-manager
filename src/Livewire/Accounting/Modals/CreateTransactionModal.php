<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

use Illuminate\Support\Carbon;
use jfsullivan\ApexUi\Modal\FormModalComponent;
use jfsullivan\CommunityManager\Livewire\Accounting\Traits\HasTransactionForm;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class CreateTransactionModal extends FormModalComponent
{
    use HasTransactionForm;

    public string $modalName = 'create-transaction';

    public $user_id;

    public $records = [];

    /** Accept an optional pre-selected user (and bulk selection) from the opener. */
    #[On('open-{modalName}')]
    public function openModal($id = null, $returnTo = null, $user_id = null, array $records = []): void
    {
        $this->user_id = $user_id;
        $this->records = $records;

        parent::openModal($id, $returnTo);
    }

    protected function initForm(): void
    {
        $this->form->reset();
        $this->form->user_id = $this->user_id;
        $this->form->transacted_at = Carbon::now('UTC')->toUserTimezone('Y-m-d H:i:s');
    }

    public function mount(): void
    {
        $this->form->user_id = $this->user_id;
        $this->form->transacted_at = Carbon::now('UTC')->toUserTimezone('Y-m-d H:i:s');
    }

    #[Computed]
    public function userSearchTerm()
    {
        if (! $this->user_id) {
            return null;
        }

        $userClass = app(config('community-manager.user_model'));

        return $userClass::withFullName()->where('id', $this->user_id)->value('full_name');
    }

    #[Computed]
    public function transferUserSearchTerm()
    {
        return null;
    }

    public function save(): void
    {
        $this->validate();

        $transaction = $this->form->store();

        $this->closeModal();

        $this->dispatch('transaction-created', $transaction);
    }
}
