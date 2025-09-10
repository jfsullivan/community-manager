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
        $community = $this->user->currentCommunity;
        return $community ? $community->load('owner') : null;
    }

    public function render()
    {
        return view('community-manager::livewire.dashboard', [
            'community' => $this->community,
        ])->layout('components.layouts.app');
    }
}
