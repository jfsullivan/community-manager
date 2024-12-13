<?php

namespace jfsullivan\CommunityManager\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class CommunityMenu extends Component
{
    #[On('refresh-community-menu')]
    public function render()
    {
        return view('community-manager::livewire.community-menu');
    }
}
