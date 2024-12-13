<?php

namespace jfsullivan\CommunityManager\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Header extends Component
{
    public $isAdminPage = false;
    
    #[On('refresh-community-list')]
    public function render()
    {
        return view('community-manager::livewire.header');
    }
}
