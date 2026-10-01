<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use jfsullivan\ApexUi\Modal\FormModalComponent;
use jfsullivan\CommunityManager\Livewire\Accounting\Traits\HasTransactionForm;
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
        $this->form->transacted_date = Carbon::now('UTC')->toUserTimezone('Y-m-d');
        $this->form->transacted_time = Carbon::now('UTC')->toUserTimezone('H:i');
    }

    public function mount(): void
    {
        $this->form->user_id = $this->user_id;
        $this->form->transacted_date = Carbon::now('UTC')->toUserTimezone('Y-m-d');
        $this->form->transacted_time = Carbon::now('UTC')->toUserTimezone('H:i');
    }

    public function save(): void
    {
        $community = Auth::user()?->currentCommunity;

        if ($community === null || Gate::denies('create-community-transaction', [$community])) {
            $this->dispatch('notify', type: 'error', title: 'Not allowed', message: 'You can’t add transactions in this community.');
            $this->closeModal();

            return;
        }

        $this->validate();

        $transaction = $this->form->store();

        $this->closeModal();

        $this->dispatch('transaction-created', $transaction);
    }
}
