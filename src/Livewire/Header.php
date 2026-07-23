<?php

namespace jfsullivan\CommunityManager\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Header extends Component
{
    public $isAdminPage = false;

    /** Show the hamburger that opens the layout's left slide-over menu (mobile only). */
    public $hasLeftMenu = false;

    #[On('refresh-community-list')]
    public function render()
    {
        return view('community-manager::livewire.header');
    }
}
