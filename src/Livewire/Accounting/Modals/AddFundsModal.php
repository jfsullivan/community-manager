<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

use Illuminate\Support\Facades\Auth;
use jfsullivan\CommunityManager\Models\Community;
use Livewire\Attributes\Computed;
use LivewireUI\Modal\ModalComponent;

class AddFundsModal extends ModalComponent
{
    public $community_id;

    public static function modalMaxWidth(): string
    {
        return 'md';
    }

    public static function destroyOnClose(): bool
    {
        return true;
    }

    #[Computed]
    public function community()
    {
        return (is_null($this->community_id)) ? Auth::user()->currentCommunity : Community::find($this->community_id);
    }

    #[Computed]
    public function paymentMethods()
    {
        return $this->community->paymentMethods;
    }

    public function render()
    {
        return view('community-manager::livewire.accounting.add-funds-modal');
    }
}
