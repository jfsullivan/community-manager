<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

use jfsullivan\CommunityManager\Livewire\Accounting\Traits\HasTransactionForm;
use Livewire\Attributes\Computed;
use LivewireUI\Modal\ModalComponent;

class UpdateTransactionModal extends ModalComponent
{
    use HasTransactionForm;

    public $transaction_id;

    public function mount()
    {
        $this->form->setTransaction($this->transaction);
    }

    #[Computed]
    public function transaction()
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        return $transactionClass::find($this->transaction_id);
    }

    #[Computed]
    public function userSearchTerm()
    {
        if (! $this->transaction && ! $this->user_id) {
            return null;
        }

        if ($this->transaction) {
            return $this->transaction->user()->withFullName()->first()->full_name;
        }
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

        if ($this->transaction) {
            return $this->transaction->transferPartner()->withFullName()->first()->full_name;
        }
    }

    public function save()
    {
        $this->validate();
        
        $transaction = $this->form->update();

        $this->closeModal();

        $this->dispatch('transaction-updated', $transaction);
    }
}
