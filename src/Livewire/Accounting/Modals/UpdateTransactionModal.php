<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

use jfsullivan\ApexUi\Modal\FormModalComponent;
use jfsullivan\CommunityManager\Livewire\Accounting\Traits\HasTransactionForm;
use Livewire\Attributes\Computed;

class UpdateTransactionModal extends FormModalComponent
{
    use HasTransactionForm;

    public string $modalName = 'update-transaction';

    /** @deprecated mount-time id kept for backwards compatibility; opening passes the id on the open event. */
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

        $transactionClass = app(config('community-manager.transaction_model'));

        return $transactionClass::find($transactionId);
    }

    #[Computed]
    public function userSearchTerm()
    {
        if (! $this->transaction) {
            return null;
        }

        return $this->transaction->user()->withFullName()->first()->full_name;
    }

    #[Computed]
    public function transferUserSearchTerm()
    {
        if (! $this->transaction) {
            return null;
        }

        if (! $this->transaction->transferPartner()->exists()) {
            return null;
        }

        return $this->transaction->transferPartner()->withFullName()->first()->full_name;
    }

    public function save(): void
    {
        $this->validate();

        $transaction = $this->form->update();

        $this->closeModal();

        $this->dispatch('transaction-updated', $transaction);
    }
}
