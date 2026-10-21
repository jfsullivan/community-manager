<?php

namespace jfsullivan\CommunityManager\Livewire\Pages;

use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Where a member lands when they open something that has been disabled (an
 * old link or bookmark): a short "isn't available" note with no reason, since
 * only its owner and admins are told why. It names nothing. The route allows
 * the kinds in `disabling.unavailable_kinds` (e.g. community, pool).
 */
class UnavailablePage extends Component
{
    #[Locked]
    public string $kind = 'community';

    // Accepting the route parameter explicitly: defining mount() turns off
    // Livewire's automatic route-parameter assignment.
    public function mount(string $kind): void
    {
        $this->kind = $kind;
    }

    public function render()
    {
        return view('community-manager::livewire.pages.unavailable-page')
            ->layout('components.layouts.app');
    }
}
