<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use jfsullivan\ApexUi\Modal\FormModalComponent;
use jfsullivan\CommunityManager\Livewire\Accounting\Traits\HasTransactionForm;
use Livewire\Attributes\Computed;

class UpdateTransactionModal extends FormModalComponent
{
    use HasTransactionForm;

    public string $modalName = 'update-transaction';

    /** Mount-time id kept for backwards compatibility; opening normally passes the id on the open event. */
    public $transaction_id;

    public function mount(): void
    {
        if ($this->transaction_id) {
            $this->form->setTransaction($this->transaction);
        }
    }

    protected function initForm(): void
    {
        $this->transaction_id = $this->modelId;

        unset($this->transaction);

        $this->form->reset();

        if ($this->transaction) {
            $this->form->setTransaction($this->transaction);
        }
    }

    #[Computed]
    public function transaction()
    {
        $transactionId = $this->modelId ?? $this->transaction_id;

        if (! $transactionId) {
            return null;
        }

        // Only a transaction in the current community, for someone allowed to
        // edit it there: the modal is mounted on member-facing pages, so the id
        // it's opened with can't be trusted.
        $community = Auth::user()?->currentCommunity;

        if ($community === null || Gate::denies('edit-community-transaction', [$community])) {
            return null;
        }

        $transactionClass = app(config('community-manager.transaction_model'));

        return $transactionClass::where('community_id', $community->id)->find($transactionId);
    }

    public function save(): void
    {
        if ($this->transaction === null) {
            $this->dispatch('notify', type: 'error', title: 'Not allowed', message: 'You can’t edit this transaction.');
            $this->closeModal();

            return;
        }

        // A transaction stays in its community; the form field isn't trusted.
        $this->form->community_id = $this->transaction->community_id;

        $this->validate();

        $transaction = $this->form->update();

        $this->closeModal();

        $this->dispatch('transaction-updated', $transaction);
    }
}
