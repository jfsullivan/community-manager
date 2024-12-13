<?php

namespace jfsullivan\CommunityManager\Livewire\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use jfsullivan\CommunityManager\Models\Community;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    use AuthorizesRequests;

    public $community_id;

    public function mount()
    {
        $this->community_id = Auth::user()->current_community_id;
    }

    #[Computed]
    public function community()
    {
        return Community::find(Auth::user()->current_community_id);
    }

    public function render()
    {
        return view('community-manager::livewire.admin.dashboard')
            ->layout(config('community-manager.admin_layout'));
    }
}
