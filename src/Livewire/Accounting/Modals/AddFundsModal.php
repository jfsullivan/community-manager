<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

use Illuminate\Support\Facades\Auth;
use jfsullivan\ApexUi\Modal\FormModalComponent;
use jfsullivan\CommunityManager\Models\Community;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class AddFundsModal extends FormModalComponent
{
    public string $modalName = 'add-funds';

    public $community_id;

    /** Accept an optional explicit community from the opener (defaults to the user's current community). */
    #[On('open-{modalName}')]
    public function openModal($id = null, $returnTo = null, $community_id = null): void
    {
        $this->community_id = $community_id;

        unset($this->community, $this->paymentMethods);

        parent::openModal($id, $returnTo);
    }

    protected function initForm(): void {}

    /** Informational dialog; nothing to persist. */
    public function save(): void
    {
        $this->closeModal();
    }

    #[Computed]
    public function community()
    {
        return (is_null($this->community_id)) ? Auth::user()?->currentCommunity : Community::find($this->community_id);
    }

    #[Computed]
    public function paymentMethods()
    {
        return $this->community?->paymentMethods ?? collect();
    }

    public function render()
    {
        return view('community-manager::livewire.accounting.add-funds-modal');
    }
}
