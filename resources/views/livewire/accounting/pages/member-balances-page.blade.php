<div class="flex flex-col items-center w-full pb-8">

    <x-breadcrumbs class="bg-gray-100">
        <x-breadcrumbs.item url="{{ route('community.admin.index') }}"><x-apexicon-open.speedometer class="flex-shrink-0 w-4 h-4 stroke-2 sm:h-5 sm:w-5" /></x-breadcrumbs.item>
        <x-breadcrumbs.dividers.chevron />
        <x-breadcrumbs.item url="{{ route('community.admin.accounting.index') }}">Accounting</x-breadcrumbs.item>
        <x-breadcrumbs.dividers.chevron />
        <x-breadcrumbs.item>Member Balances</x-breadcrumbs.item>
    </x-breadcrumbs>

    <div class="flex justify-center w-full bg-white border-b border-gray-200">
        <x-heading.page-heading class="w-full px-2 sm:px-0">
            <x-slot name="label">Member Balances</x-slot>
            <x-slot name="description">Current account balance for each member of this community.</x-slot>

            <x-slot name="actions">
                @if(Gate::allows('create-community-transaction', $this->community))
                    <x-button.outline size="xs" color="gray" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.create-transaction-modal' })">
                        <x-slot name="leadingIcon"><x-apexicon-open.plus class="w-4 h-4 stroke-2" /></x-slot>
                        Add Transaction
                    </x-button.outline>
                @endif
            </x-slot>
        </x-heading.page-heading>
    </div>

    <div class="flex flex-col w-full">
        <x-infolist class="divide-y divide-gray-200" searchable selectable sortable :total-records="$this->records->total()" :records-shown="$this->records->count()">
            <x-slot name="bulkActions">
                <x-dropdown.link type="button" class="flex items-center space-x-2" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.create-transaction-modal', arguments: { { records: $wire.selected }})">
                    <x-apexicon-open.trash class="w-5 h-5 text-gray-400 stroke-2 group-hover:text-gray-500"/><span class="whitespace-nowrap">Add Bulk Transaction</span>
                </x-dropdown.link>
            </x-slot>
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

            <x-slot name="heading">
                <x-infolist.heading>
                    <x-infolist.heading.column sort-key="name" sort-direction="{{ $sorts['name'] ?? null }}" class="justify-start col-span-8 md:col-span-7">Name</x-infolist.heading.column>
                    <x-infolist.heading.column sort-key="last_activity" sort-direction="{{ $sorts['last_activity'] ?? null }}" class="justify-end hidden col-span-2 sm:flex">Last Activity</x-infolist.heading.column>
                    <x-infolist.heading.column sort-key="balance" sort-direction="{{ $sorts['balance'] ?? null }}" class="justify-end col-span-4 pr-16 md:col-span-3 sm:pr-12">Balance</x-infolist.heading.column>
                    <x-slot name="actions">
                        <x-infolist.heading.column class="w-10 sm:w-16"></x-infolist.heading.column>
                    </x-slot>
                </x-infolist.heading>
            </x-slot>

            @forelse ($this->records as $member)
                <x-infolist.item wire:key="member-{{ $member->id }}" :key="$member->id">
                    <x-slot name="columns">
                        <x-infolist.item.primary-column class="col-span-8 md:col-span-7">
                            <x-slot name="avatar">
                                {{-- {{ $member->profile_photo_url }} --}}
                                {{-- {{ $member->full_name }} --}}
                                <x-profile-photo class="w-8 h-8 sm:h-10 sm:w-10" :url="$member->profile_photo_url" :name="$member->full_name" />
                            </x-slot>
                            <x-slot name="label">
                                {{ $member->full_name }}
                            </x-slot>
                            <x-slot name="subLabel">
                                <span class="hidden sm:inline">
                                    <x-apexicon-open.mail class="mr-1.5 h-4 w-4 text-gray-500 stroke-1.5" />
                                </span>
                                <a href="mailto:{{ $member->email }}" class="w-full truncate max-w-fit hover:underline">{{ $member->email }}</a>
                            </x-slot>
                        </x-infolist.item.primary-column>

                        <x-infolist.item.column class="justify-end hidden col-span-2 text-xs md:flex md:text-sm">
                            @if(is_null($member->currentMembership->last_accessed_at))
                                -
                            @else
                                {{ \Illuminate\Support\Carbon::parse($member->currentMembership->last_accessed_at)->diffForHumans() }}
                            @endif
                        </x-infolist.item.column>

                        <x-infolist.item.column class="justify-end col-span-4 md:col-span-3">
                            <div class="flex items-center space-x-2 sm:space-x-4">
                                <x-money :amount="$member->balance" class="font-medium" formatted />
                                @if(Gate::allows('create-community-transaction', $this->community))
                                    <x-button.circular class="bg-primary-50 group-hover:bg-primary-200" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.create-transaction-modal', arguments: { user_id: {{ $member->id }} } })">
                                        <x-apexicon-open.plus class="w-4 h-4 stroke-2 sm:w-5 sm:h-5 text-primary-700" />
                                    </x-button.circular>
                                @endif
                            </div>
                        </x-infolist.item.column>
                    </x-slot>

                    <x-slot name="actions">
                        <div class="flex items-center justify-center w-10 sm:w-16">
                            <a href="{{ route('community.admin.accounting.member.transactions', [$member->id]) }}">
                                <x-apexicon-open.chevron-right class="w-5 h-5 text-gray-500 stroke-2" />
                            </a>
                        </div>
                    </x-slot>
                </x-infolist.item>
            @empty
                <x-empty-state class="flex w-full py-8">
                    <x-slot name="icon">
                        <x-icons.featured-double inner-color="bg-primary-100" class="bg-primary-50">
                            <x-apexicon-open.users class="w-5 h-5 stroke-2 text-primary-600"/>
                        </x-icons.featured-double>
                    </x-slot>
                    <x-slot name="title">
                        No members found
                    </x-slot>
                    <x-slot name="description">
                        There aren't any members that meet that criteria.
                    </x-slot>
                </x-empty-state>
            @endforelse

            <x-slot name="footer">
                <div class="flex justify-between flex-1 w-full">
                    {{ $this->records->links('ui-kit::components.pagination.standard.index') }}
                </div>
            </x-slot>
        </x-infolist>
    </div>
</div>
