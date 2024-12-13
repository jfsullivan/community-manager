<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

use Illuminate\Support\Carbon;
use JamesMills\LaravelTimezone\Facades\Timezone;
use jfsullivan\CommunityManager\Livewire\Accounting\Traits\HasTransactionForm;
use Livewire\Attributes\Computed;
use LivewireUI\Modal\ModalComponent;

class CreateTransactionModal extends ModalComponent
{
    use HasTransactionForm;

    public $user_id;

    public $records = [];

    public function mount()
    {
        $this->form->user_id = $this->user_id;
        $this->form->transacted_at = Timezone::convertToLocal(Carbon::now('UTC'), 'Y-m-d H:i:s');
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

    public function save()
    {
        $this->validate();

        $transaction = $this->form->store();

        $this->closeModal();

        $this->dispatch('transaction-created', $transaction);
    }
}
