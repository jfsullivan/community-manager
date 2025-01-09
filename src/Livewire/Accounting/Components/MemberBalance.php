<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Components;

use Brick\Money\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MemberBalance extends Component
{
    public $formatted = true;

    public $class = '';

    public $community_id;

    public $user_id;

    public $selectable = true;

    #[Computed]
    public function community()
    {
        $communityClass = app(config('community-manager.community_model'));

        return ($this->community_id)
            ? $communityClass::find($this->community_id)
            : Auth::user()->currentCommunity;
    }

    #[Computed]
    public function user()
    {
        $userClass = app(config('community-manager.user_model'));

        return ($this->user_id)
            ? $userClass::find($this->user_id)
            : Auth::user();
    }

    #[Computed]
    public function memberBalance()
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        $total = $transactionClass::query()
            ->selectRaw('SUM( amount ) as total')
            ->where('community_id', $this->community->id)
            ->where('user_id', $this->user->id)
            ->groupBy('user_id')
            ->value('total');

        return Money::ofMinor($total ?? 0, $this->community->currency);
    }

    #[On('transaction-created')]
    #[On('transaction-deleted')]
    #[On('transaction-updated')]
    public function render()
    {
        return view('community-manager::livewire.accounting.components.member-balance');
    }
}
