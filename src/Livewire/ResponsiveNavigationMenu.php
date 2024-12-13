<?php

namespace jfsullivan\CommunityManager\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class ResponsiveNavigationMenu extends Component
{
    #[On('refresh-community-menu')]
    public function render()
    {
        return view('community-manager::livewire.responsive-navigation-menu');
    }
}
