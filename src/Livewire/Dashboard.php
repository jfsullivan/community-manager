<?php

namespace jfsullivan\CommunityManager\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public $showPaymentMethodsModal;

    public function getUserProperty()
    {
        return Auth::user();
    }

    public function getCommunityProperty()
    {
        return $this->user->currentCommunity;
    }

    public function render()
    {
        return view('community-manager::livewire.dashboard', [
            'community' => $this->community,
        ]);
    }
}
