{{-- Community member row — the base member-manager row plus Confirm/Reject
     affordances on pending (start_at IS NULL) rows so admins can approve or
     turn down join requests directly from the roster. --}}
<x-apex::grid.item wire:key="member-{{ $member->id }}" wire:model="selected">
    <x-apex::grid.item.column variant="primary" class="col-span-12 md:col-span-8 justify-start">
        <div class="flex items-center space-x-2">
            <x-profile-photo class="w-8 h-8 sm:h-10 sm:w-10" :url="$member->profile_photo_url" :name="$member->full_name" />
            <div class="flex flex-col">
                @if ($detailsUrl = $this->memberDetailsUrl($member))
                    <a href="{{ $detailsUrl }}" class="hover:underline" x-on:click.stop>{{ $member->full_name }}</a>
                @else
                    <span>{{ $member->full_name }}</span>
                @endif
                <span class="flex items-center text-xs font-normal text-gray-500 dark:text-zinc-400">
                    <span class="hidden sm:inline">
                        <x-apex::icon name="apex-ui.mail" class="mr-1.5 h-4 w-4 text-gray-500 dark:text-zinc-400 stroke-1.5" />
                    </span>
                    <a href="mailto:{{ $member->email }}" class="w-full truncate max-w-fit hover:underline">{{ $member->email }}</a>
                </span>
            </div>
        </div>
    </x-apex::grid.item.column>

    @if ($this->showsColumn('role'))
        <x-apex::grid.item.column class="hidden md:flex md:col-span-2 justify-start">
            {{-- An unconfirmed member's role isn't meaningful yet — show where
                 they are in the join flow instead (mirrors member-manager's row).
                 Banned takes precedence. --}}
            @if (($member->latest_status_name ?? null) === 'banned')
                <x-apex::badge color="red" size="sm">Banned</x-apex::badge>
            @elseif ($member->membership_status == 'pending')
                @if (! is_null($member->invitation_id) && is_null($member->invitation_accepted_at))
                    <x-apex::badge color="amber" size="sm">Invited</x-apex::badge>
                @else
                    <x-apex::badge color="zinc" size="sm">Pending</x-apex::badge>
                @endif
            @else
                <x-apex::badge :color="$member->member_role_color ?? 'blue'" size="sm">{{ $member->member_role_name }}</x-apex::badge>
            @endif
        </x-apex::grid.item.column>
    @endif

    @if ($this->showsColumn('type'))
        <x-apex::grid.item.column class="col-span-4 md:col-span-3 justify-start">
            @if($member->membership_status == 'pending')
                <x-apex::badge color="zinc" size="sm">Pending</x-apex::badge>
            @elseif($member->membership_status == 'former')
                <x-apex::badge color="red" size="sm">Expired</x-apex::badge>
            @else
                <x-apex::badge :color="$member->membership_type_color ?? 'blue'" size="sm">{{ $member->membership_type_name }}</x-apex::badge>
            @endif
        </x-apex::grid.item.column>
    @endif

    @if ($this->showsColumn('last_activity'))
        <x-apex::grid.item.column class="hidden md:flex md:col-span-3 justify-end text-xs md:text-sm">
            @if($member->membership_status == 'pending')
                <div class="flex items-center gap-2">
                    <x-apex::button variant="primary" size="xs" wire:click="confirmMembership({{ $member->user_id }})">Confirm</x-apex::button>
                    <x-apex::button variant="ghost" size="xs" wire:click="rejectMembership({{ $member->user_id }})">Reject</x-apex::button>
                </div>
            @elseif(is_null($member->last_accessed_at))
                @if(is_null($member->invitation_id))
                    <x-apex::button variant="ghost" size="xs" wire:click="sendInvitation({{ $member->id }}, null)" class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">Send Invite</x-apex::button>
                @else
                    @if(!is_null($member->invitation_accepted_at))
                        <div class="flex flex-col items-end">
                            <div class="text-xs text-gray-500 dark:text-zinc-400">Invitation Accepted</div>
                            <div class="text-xs text-gray-500 dark:text-zinc-400">{{ \Illuminate\Support\Carbon::parse($member->invitation_accepted_at)->diffForHumans() }}</div>
                        </div>
                    @else
                        <div class="flex flex-col items-end">
                            <div class="text-xs text-gray-500 dark:text-zinc-400">Invitation Sent</div>
                            <div class="text-xs text-gray-500 dark:text-zinc-400">{{ \Illuminate\Support\Carbon::parse($member->invitation_last_sent_at)->diffForHumans() }}</div>
                            <x-apex::button variant="ghost" size="xs" wire:click="sendInvitation({{ $member->id }}, {{ $member->invitation_id }})" class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">Resend</x-apex::button>
                        </div>
                    @endif
                @endif
            @else
                {{ \Illuminate\Support\Carbon::parse($member->last_accessed_at)->diffForHumans() }}
            @endif
        </x-apex::grid.item.column>
    @endif

    <x-slot:actions>
        <x-apex::grid.item.column.actions.dropdown>
            @if (($member->latest_status_name ?? null) === 'banned')
                <x-apex::menu.item icon="apex-ui.check" wire:click="liftBan({{ $member->user_id }})">Lift Ban</x-apex::menu.item>
            @else
                @if ($member->membership_status == 'pending')
                    <x-apex::menu.item icon="apex-ui.check" wire:click="confirmMembership({{ $member->user_id }})">Confirm Request</x-apex::menu.item>
                    <x-apex::menu.item icon="apex-ui.x-close" wire:click="rejectMembership({{ $member->user_id }})">Reject Request</x-apex::menu.item>
                @endif

                @if ($this->showsColumn('role'))
                    <x-apex::menu.item icon="apex-ui.user-settings" wire:click="$dispatch('open-change-member-role', { record_id: '{{ $member->user_id }}' })">Change Role</x-apex::menu.item>
                @endif

                @if ($this->showsColumn('type'))
                    <x-apex::menu.item icon="apex-ui.passport" wire:click="$dispatch('open-change-membership-type', { record_id: '{{ $member->user_id }}' })">Change Membership</x-apex::menu.item>
                @endif

                @if ($member->membership_status == 'former')
                    <x-apex::menu.item icon="apex-ui.refresh" wire:click="restartMembership({{ $member->user_id }})">Restart Membership</x-apex::menu.item>
                @endif

                <x-apex::menu.item icon="apex-ui.users-x" wire:click="$dispatch('open-remove-members', { id: {{ $member->user_id }} })">Remove Member</x-apex::menu.item>
            @endif
        </x-apex::grid.item.column.actions.dropdown>
    </x-slot:actions>
</x-apex::grid.item>
