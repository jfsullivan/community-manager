<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

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

        $transactionClass = app(config('community-manager.transaction_model'));

        return $transactionClass::find($transactionId);
    }

    public function save(): void
    {
        $this->validate();

        $transaction = $this->form->update();

        $this->closeModal();

        $this->dispatch('transaction-updated', $transaction);
    }
}
