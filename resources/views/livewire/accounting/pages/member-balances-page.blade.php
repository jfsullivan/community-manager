<div class="flex flex-col items-center w-full pb-8">

    <x-slot name="breadcrumbs">
        <x-apex::breadcrumbs.item href="{{ route('community.admin.accounting.index') }}">Accounting</x-apex::breadcrumbs.item>
        <x-apex::breadcrumbs.item>Member Balances</x-apex::breadcrumbs.item>
    </x-slot>

    <x-apex::section-header
        heading="Member Balances"
        subheading="Current account balance for each member of this community."
    >
        <x-slot:actions>
            @if(Gate::allows('create-community-transaction', $this->community))
                <x-apex::button size="sm" variant="primary" icon="apex-ui.plus" wire:click="$dispatch('open-create-transaction')">
                    Add Transaction
                </x-apex::button>
            @endif
        </x-slot:actions>
    </x-apex::section-header>

    <div class="flex flex-col w-full py-4 sm:px-4">
        <x-apex::grid card bleed selectable striped searchable
            class="w-full grid-cols-16"
            wire:model="selected"
        >
            <x-slot:bulkActions>
                <x-apex::menu.item icon="apex-ui.plus" wire:click="$dispatch('open-create-transaction', { records: $wire.selected })">Add Bulk Transaction</x-apex::menu.item>
            </x-slot:bulkActions>

            {{-- The shared filter row: Balance and Members (Current by default, so
                 former and banned members stay hidden until asked for). --}}
            <x-slot:filterRow>
                <x-apex::filter-row
                    :active="($balanceFilter !== 'all' ? 1 : 0) + ($this->memberStatusFilterChip() ? 1 : 0)"
                    clear-action="clearBalanceFilters"
                >
                    <x-apex::input.select label="Balance" wire:model.live="balanceFilter">
                        @foreach ($this->balanceFilterOptions() as $value => $label)
                            <x-apex::input.select.option value="{{ $value }}">{{ $label }}</x-apex::input.select.option>
                        @endforeach
                    </x-apex::input.select>

                    <x-apex::input.select label="Members" wire:model.live="memberStatusFilter">
                        @foreach ($this->memberStatusFilterOptions() as $value => $label)
                            <x-apex::input.select.option value="{{ $value }}">{{ $label }}</x-apex::input.select.option>
                        @endforeach
                    </x-apex::input.select>
                </x-apex::filter-row>
            </x-slot:filterRow>

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
                                <span class="flex items-center text-xs font-normal text-gray-500 dark:text-zinc-400">
                                    <span class="hidden sm:inline">
                                        <flux:icon name="apex-ui.mail" class="mr-1.5 h-4 w-4 text-gray-500 dark:text-zinc-400 stroke-1.5" />
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
                                <flux:icon name="apex-ui.chevron-right" class="w-5 h-5 text-gray-500 dark:text-zinc-400 stroke-2" />
                            </a>
                        </div>
                    </x-slot:actions>
                </x-apex::grid.item>
            @empty
                <x-apex::grid.item :selectable="false">
                    <x-apex::grid.item.column class="col-span-full justify-center">
                        {{-- A community or pool always has members (its owner), so an empty list is
                             a search/filter miss: clearFilters() resets the search and every filter. --}}
                        <x-apex::empty-state icon="apex-ui.search" heading="No members found" subheading="We couldn't find any members that meet that criteria.">
                            <x-slot:actions>
                                <x-apex::button variant="outline" wire:click="clearFilters">Clear search and filters</x-apex::button>
                            </x-slot:actions>
                        </x-apex::empty-state>
                    </x-apex::grid.item.column>
                </x-apex::grid.item>
            @endforelse

            <x-slot name="footer">
                <x-apex::grid.load-more :records="$this->records" class="mt-2" />
            </x-slot>
        </x-apex::grid>
    </div>

    <livewire:community-manager.accounting.modals.create-transaction-modal />
</div>
