<?php

namespace jfsullivan\CommunityManager\Livewire\Pages;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use jfsullivan\CommunityManager\Actions\Communities\RequestCommunityReenableAction;
use jfsullivan\CommunityManager\Models\Community;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * For a disabled community's owner and admins (and site admins): when and
 * why it was disabled, what that means, and a way to ask for it back. Every
 * page in the community sends them here (EnsureCommunityIsEnabled), as do the
 * disable/decline notices. Its members get the reasonless "isn't available"
 * page instead, and once the community is enabled again everyone goes home.
 *
 * Optional `disabling.contact_view`: a view rendered at the bottom (e.g. how
 * to reach the site admins).
 *
 * @property-read Community $community
 */
class DisabledCommunityPage extends Component
{
    public int $communityId;

    #[Validate('required|string|max:2000', as: 'message')]
    public string $message = '';

    // Accepting the route parameter explicitly: defining mount() turns off
    // Livewire's automatic route-parameter assignment.
    public function mount(int $community_id)
    {
        $this->communityId = $community_id;

        if (! $this->community->isDisabled()) {
            return $this->redirectRoute('home', navigate: true);
        }

        // Anyone else isn't told why.
        if (! $this->canView()) {
            return $this->redirectRoute('unavailable', ['kind' => 'community'], navigate: true);
        }
    }

    #[Computed]
    public function community(): Community
    {
        return config('community-manager.community_model')::query()->where('is_personal', false)->findOrFail($this->communityId);
    }

    /** The community's owner and admins, and site admins. */
    protected function canView(): bool
    {
        $user = Auth::user();

        return $user !== null
            && (Gate::allows(config('community-manager.site_admin_ability', 'manage-site')) || $this->community->isRunBy($user));
    }

    /** Ask the site admins to enable the community again. */
    public function requestReenable(): void
    {
        abort_unless($this->canView() && $this->community->isDisabled(), 403);

        $this->validate();

        app(RequestCommunityReenableAction::class)->request($this->community, Auth::user(), $this->message);

        unset($this->community);
        $this->reset('message');

        $this->dispatch('notify', type: 'success', title: 'Request sent', message: config('app.name').' will review your request.');
    }

    public function render()
    {
        return view('community-manager::livewire.pages.disabled-community-page')
            ->layout('components.layouts.app');
    }
}
