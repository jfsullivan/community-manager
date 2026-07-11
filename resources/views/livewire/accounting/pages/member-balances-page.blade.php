<div class="flex flex-col items-center w-full pb-8">

    <x-slot name="breadcrumbs">
        <x-apex::breadcrumbs.item href="{{ route('community.admin.accounting.index') }}">Accounting</x-apex::breadcrumbs.item>
        <x-apex::breadcrumbs.item>Member Balances</x-apex::breadcrumbs.item>
    </x-slot>

    <div class="flex justify-center w-full bg-white border-b border-gray-200">
        <div class="w-full flex flex-col md:flex-row md:justify-between space-y-2 md:space-y-0 py-5 px-2 md:px-4 bg-white">
            <div class="w-full flex flex-col">
                <x-apex::heading size="xl" class="mb-0! font-semibold!">Member Balances</x-apex::heading>
                <x-apex::text>Current account balance for each member of this community.</x-apex::text>
            </div>
            <div class="flex items-center space-x-2">
                @if(Gate::allows('create-community-transaction', $this->community))
                    <x-apex::button size="sm" icon="apex-ui.plus" wire:click="$dispatch('open-create-transaction')">
                        Add Transaction
                    </x-apex::button>
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-col w-full">
        <x-apex::grid flush selectable striped searchable
            class="w-full grid-cols-16"
            wire:model="selected"
        >
            <x-slot:bulkActions>
                <x-apex::menu.item icon="apex-ui.plus" wire:click="$dispatch('open-create-transaction', { records: $wire.selected })">Add Bulk Transaction</x-apex::menu.item>
            </x-slot:bulkActions>

            <x-slot name="filterButtons">
                <x-button-group wire:model.live="balanceFilter" class="grid grid-cols-4">
                    <x-button-group.button name="all" class="text-xs text-gray-800 sm:text-sm ring-gray-200 hover:bg-gray-50 ">
                        <span class="py-px">View All</span>
                    </x-button-group.button>
                    <x-button-group.button name="positive" class="text-xs text-gray-800 sm:text-sm ring-gray-200 hover:bg-gray-50">
                        <span class="py-px">Positive</span>
                    </x-button-group.button>
                    <x-button-group.button name="negative" class="text-xs text-gray-800 sm:text-sm ring-gray-200 hover:bg-gray-50">
                        <span class="py-px">Negative</span>
                    </x-button-group.button>
                    <x-button-group.button name="zero" class="text-xs text-gray-800 sm:text-sm ring-gray-200 hover:bg-gray-50">
                        <span class="py-px">No Balance</span>
                    </x-button-group.button>
                </x-button-group>
            </x-slot>

            <x-slot:header actions-variant="icon">
                <x-apex::grid.header.column class="justify-start col-span-11 md:col-span-9" sortable sort-key="name" :sort-data="$sorts">Name</x-apex::grid.header.column>
                <x-apex::grid.header.column class="justify-end hidden col-span-3 md:flex" sortable sort-key="last_activity" :sort-data="$sorts">Last Activity</x-apex::grid.header.column>
                <x-apex::grid.header.column class="justify-end col-span-5 md:col-span-4" sortable sort-key="balance" :sort-data="$sorts">Balance</x-apex::grid.header.column>
            </x-slot:header>

            @forelse ($this->records as $member)
                <x-apex::grid.item wire:key="member-{{ $member->id }}" wire:model="selected">
                    <x-apex::grid.item.column variant="primary" class="justify-start col-span-11 md:col-span-9">
                        <div class="flex items-center space-x-2">
                            <x-profile-photo class="w-8 h-8 sm:h-10 sm:w-10" :url="$member->profile_photo_url" :name="$member->full_name" />
                            <div class="flex flex-col">
                                <span>{{ $member->full_name }}</span>
                                <span class="flex items-center text-xs font-normal text-gray-500">
                                    <span class="hidden sm:inline">
                                        <flux:icon name="apex-ui.mail" class="mr-1.5 h-4 w-4 text-gray-500 stroke-1.5" />
                                    </span>
                                    <a href="mailto:{{ $member->email }}" class="w-full truncate max-w-fit hover:underline">{{ $member->email }}</a>
                                </span>
                            </div>
                        </div>
                    </x-apex::grid.item.column>

                    <x-apex::grid.item.column class="justify-end hidden col-span-3 text-xs md:flex md:text-sm">
                        @if(is_null($member->currentMembership->last_accessed_at))
                            -
                        @else
                            {{ \Illuminate\Support\Carbon::parse($member->currentMembership->last_accessed_at)->diffForHumans() }}
                        @endif
                    </x-apex::grid.item.column>

                    <x-apex::grid.item.column class="justify-end col-span-5 md:col-span-4">
                        <div class="flex items-center space-x-2 sm:space-x-4">
                            <x-money :amount="$member->balance" class="font-medium" formatted />
                            @if(Gate::allows('create-community-transaction', $this->community))
                                <x-apex::button variant="ghost" size="sm" icon="apex-ui.plus" inset="top bottom" wire:click="$dispatch('open-create-transaction', { user_id: {{ $member->id }} })" />
                            @endif
                        </div>
                    </x-apex::grid.item.column>

                    <x-slot:actions>
                        <div class="flex items-center justify-center w-10 sm:w-16">
                            <a href="{{ route('community.admin.accounting.member.transactions', [$member->id]) }}">
                                <flux:icon name="apex-ui.chevron-right" class="w-5 h-5 text-gray-500 stroke-2" />
                            </a>
                        </div>
                    </x-slot:actions>
                </x-apex::grid.item>
            @empty
                <x-apex::grid.item :selectable="false">
                    <x-apex::grid.item.column class="col-span-full justify-center">
                        <x-apex::empty-state icon="apex-ui.users" heading="No members found" class="mt-6 mb-6">
                            <x-slot:subheading>
                                There aren't any members that meet that criteria.
                            </x-slot:subheading>
                        </x-apex::empty-state>
                    </x-apex::grid.item.column>
                </x-apex::grid.item>
            @endforelse

            <x-slot name="footer">
                <div class="flex justify-between flex-1 w-full">
                    {{ $this->records->links() }}
                </div>
            </x-slot>
        </x-apex::grid>
    </div>

    <livewire:community-manager.accounting.modals.create-transaction-modal />
</div>
