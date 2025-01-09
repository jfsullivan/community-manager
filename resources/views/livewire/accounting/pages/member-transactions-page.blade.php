<div class="flex flex-col items-center w-full pb-8">

    <x-breadcrumbs class="bg-gray-100">
        <x-breadcrumbs.item url="{{ route('community.admin.index') }}"><x-apexicon-open.speedometer class="flex-shrink-0 w-4 h-4 stroke-2 sm:h-5 sm:w-5" /></x-breadcrumbs.item>
        <x-breadcrumbs.dividers.chevron />
        <x-breadcrumbs.item url="{{ route('community.admin.accounting.index') }}">Accounting</x-breadcrumbs.item>
        <x-breadcrumbs.dividers.chevron />
        <x-breadcrumbs.item url="{{ route('community.admin.accounting.member.balances') }}">Member Balances</x-breadcrumbs.item>
        <x-breadcrumbs.dividers.chevron />
        <x-breadcrumbs.item>{{ $this->user->full_name }} Transactions</x-breadcrumbs.item>
    </x-breadcrumbs>

    <div class="flex justify-center w-full bg-white border-b border-gray-200">
        <x-heading.page-heading class="w-full px-2">
            <x-slot name="avatar">
                <x-profile-photo class="h-14 w-14" :url="$this->user->profile_photo_url" :name="$this->user->full_name" />
            </x-slot>
            <x-slot name="label">{{ $this->user->full_name }}</x-slot>
            <x-slot name="description">
                <div class="flex items-center text-sm text-gray-600">
                    <x-apexicon-open.mail class="mr-1.5 h-5 w-5 shrink-0 text-gray-500" />
                    {{ $this->user->email }}
                </div>
            </x-slot>

            <x-slot name="actions">
                <div class="flex items-center">
                    @can('view-member-balance', $this->community)
                        <div class="flex flex-col items-center justify-center mx-4 sm:ml-0">
                            <div class="flex text-xs text-gray-400 whitespace-nowrap">Account Balance</div>
                            <livewire:community-manager::accounting.components.member-balance :user_id="$this->user->id" size="lg" :selectable="false" />
                        </div>
                    @endcan
                    @if(Gate::allows('create-community-transaction', $this->community))
                        <x-button.outline size="xs" color="gray" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.create-transaction-modal', arguments: { user_id: {{ $this->user->id }} }})">
                            <x-slot name="leadingIcon"><x-apexicon-open.plus class="w-4 h-4 stroke-2" /></x-slot>
                            Add Transaction
                        </x-button.outline>
                    @endif
                </div>
            </x-slot>
        </x-heading.page-heading>
    </div>

    <div class="flex flex-col w-full">
        <x-infolist class="border-gray-200 divide-y divide-gray-200 last:border-b" searchable selectable sortable striped :total-records="$this->records->total()" :records-shown="$this->records->count()">
            <x-slot name="bulkActions">
                <x-dropdown.link type="button" class="flex items-center space-x-2" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.delete-transaction-modal', arguments: { records: $wire.selected }})">
                    <x-apexicon-open.trash class="w-5 h-5 text-gray-400 stroke-2 group-hover:text-gray-500"/><span class="whitespace-nowrap">Delete Selected Transactions</span>
                </x-dropdown.link>
            </x-slot>

            <x-slot name="heading">
                <x-infolist.heading>
                    <x-infolist.heading.column sort-key="date" sort-direction="{{ $sorts['date'] ?? null }}" class="justify-start col-span-2">Date</x-infolist.heading.column>
                    <x-infolist.heading.column sort-key="transaction" sort-direction="{{ $sorts['transaction'] ?? null }}" class="justify-start col-span-7 lg:pl-7">Transaction</x-infolist.heading.column>
                    <div class="flex flex-col-reverse items-center justify-end w-full col-span-3 lg:grid lg:grid-cols-2 lg:gap-x-2">
                        <x-infolist.heading.column sort-key="type" sort-direction="{{ $sorts['type'] ?? null }}" class="justify-end hidden lg:flex lg:justify-start">Type</x-infolist.heading.column>
                        <x-infolist.heading.column sort-key="amount" sort-direction="{{ $sorts['amount'] ?? null }}" class="flex justify-end">Amount</x-infolist.heading.column>
                    </div>
                    <x-slot name="actions">
                        <x-infolist.heading.column class="w-10 sm:w-16"></x-infolist.heading.column>
                    </x-slot>
                </x-infolist.heading>
            </x-slot>

            @forelse ($this->records as $transaction)
                <x-infolist.item wire:key="transaction-{{ $transaction->id }}" :key="$transaction->id">
                    <x-slot name="columns">
                        <x-infolist.item.column class="justify-start col-span-2">
                            <div class="flex flex-col items-start justify-start w-full text-gray-500 lg:hidden">
                                <p class="text-xs font-medium leading-5 whitespace-nowrap sm:text-sm sm:leading-6">
                                    @displayDate($transaction->transacted_at, 'M j')
                                </p>
                                <p class="leading-4 text-2xs sm:text-xs md:text-sm sm:leading-5">
                                    @displayDate($transaction->transacted_at, 'Y')
                                </p>
                            </div>
                            <div class="hidden w-full text-sm text-gray-500 lg:block whitespace-nowrap">
                                @displayDate($transaction->transacted_at, 'M j, Y')
                            </div>
                        </x-infolist.item.column>
                        <x-infolist.item.column class="justify-start col-span-7 lg:space-x-4">
                            <div class="hidden lg:inline">
                                <x-community-manager::accounting.transactions.transaction-type-icon :type="$transaction->type->slug" />
                            </div>
                            <x-community-manager::accounting.transactions.transaction-detail :transaction="$transaction" class="font-medium text-gray-900" />
                        </x-infolist.item.column>
                        <div class="flex flex-col-reverse items-center justify-end w-full col-span-3 lg:grid lg:grid-cols-2 lg:gap-x-2">
                            <x-infolist.item.column class="flex justify-end text-sm lg:justify-start">
                                <x-community-manager::accounting.transactions.transaction-type-detail :transaction="$transaction" />
                            </x-infolist.item.column>

                            <x-infolist.item.column class="flex justify-end text-xs sm:text-sm">
                                <x-money :amount="$transaction->amount" formatted />
                            </x-infolist.item.column>
                        </div>
                    </x-slot>

                    <x-slot name="actions">
                        <div class="flex items-center justify-center w-10 sm:w-16">
                            @if(Gate::any(['edit-community-transaction', 'delete-community-transaction'], [$this->community]))
                                <x-dropdown dropdown-alignment="right" hide-arrow >
                                    <x-slot name="trigger">
                                        <x-apexicon-open.dots-vertical class="w-5 h-5 text-gray-400 cursor-pointer stroke-2 hover:text-gray-500" />
                                    </x-slot>

                                    @if(Gate::allows('edit-community-transaction', $this->community))
                                        <x-dropdown.link type="button" class="flex items-center space-x-2" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.update-transaction-modal', arguments: { transaction_id: {{ $transaction->id }} } })">
                                            <x-apexicon-open.edit class="w-5 h-5 text-gray-400 stroke-2" /><span>Edit Transaction</span>
                                        </x-dropdown.link>
                                    @endif

                                    @if(Gate::allows('delete-community-transaction', [$this->community]))
                                        <x-dropdown.link type="button" class="flex items-center space-x-2" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.delete-transaction-modal', arguments: { record_id: {{ $transaction->id }} } })">
                                            <x-apexicon-open.trash class="w-5 h-5 text-gray-400 stroke-2"/><span>Delete Transaction</span>
                                        </x-dropdown.link>
                                    @endif

                                </x-dropdown>
                            @endif
                        </div>
                    </x-slot>
                </x-infolist.item>
            @empty
                <x-empty-state class="flex w-full py-8">
                    <x-slot name="icon">
                        <x-icons.featured-double inner-color="bg-primary-100" class="bg-primary-50">
                            <x-apexicon-open.coins-swap class="w-5 h-5 stroke-2 text-primary-600"/>
                        </x-icons.featured-double>
                    </x-slot>
                    <x-slot name="title">
                        No transactions yet
                    </x-slot>
                    <x-slot name="description">
                        {{ $this->user->full_name }} doesn't have any transactions that meet that criteria.
                    </x-slot>
                    <x-slot name="actions">
                        {{-- <x-button theme="primary" size="xs" wire:click="$dispatch('openModal', { component: 'picksheets.points.game-picker', arguments: {seasonId: {{ $this->season->id }}, scoringMethodId: {{ $this->scoringMethod->id }}, round: {{ $this->round }}, picks: {{ collect($this->form->picks)->pluck('team_id') }} } })">
                            <x-slot name="leadingIcon"><x-apexicon-open.plus class="w-4 h-4 stroke-2" /></x-slot>
                            <span class="whitespace-nowrap">{{ __('Make picks') }}</span>
                        </x-button.primary> --}}
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

    {{-- <x-app.card>
        <x-slot name="header" class="border-t border-gray-200 lg:border-t-0">
            <div class="items-center w-full col-span-2 text-lg font-medium leading-6 text-gray-900 hidden2 md:flex md:leading-6">
                Transaction History
            </div>

            <div class="flex justify-end w-full col-start-3 space-x-2">
                @if(Gate::allows('create-community-transaction', $this->community))
                    <div class="hidden md:inline">
                        <x-button.outline size="xs" color="gray" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.create-transaction-modal' })">
                            <x-slot name="leadingIcon"><x-apexicon-open.plus class="w-4 h-4 stroke-2" /></x-slot>
                            Add Transaction
                        </x-button.outline>
                    </div>
                    <div class="inline md:hidden">
                        <x-button.outline size="2xs" color="gray" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.create-transaction-modal' })">
                            <x-slot name="leadingIcon"><x-apexicon-open.plus class="w-4 h-4 stroke-2" /></x-slot>
                            Add Transaction
                        </x-button.outline>
                    </div>
                @endif
            </div>
        </x-slot>

        <x-table class="w-full table-fixed sm:table-auto">
            <x-slot name="head">
                <x-table.heading sortable sort-key="date" sort-direction="{{ $sorts['date'] ?? null }}" align="left" class="w-12 px-1 sm:w-auto sm:px-6">Date</x-table.heading>
                <x-table.heading class="w-10 px-2" />
                <x-table.heading sortable sort-key="transaction" sort-direction="{{ $sorts['transaction'] ?? null }}" align="left" class="px-1 sm:w-auto sm:px-6">Transaction</x-table.heading>
                <x-table.heading sortable sort-key="type" sort-direction="{{ $sorts['type'] ?? null }}" align="left" class="hidden md:table-cell">Type</x-table.heading>
                <x-table.heading sortable sort-key="amount" sort-direction="{{ $sorts['amount'] ?? null }}" align="right" class="w-1/5 px-1 sm:w-auto sm:px-6">Amount</x-table.heading>
                <x-table.heading class="w-10 px-1 sm:w-auto sm:px-6" />
            </x-slot>

            <x-slot name="body">
                @forelse($this->records as $transaction)
                    <x-table.row wire:loading.class.delay="opacity-50" class="{{ ($loop->even) ? 'bg-gray-50' : 'bg-white' }}">
                        <x-table.cell class="px-1 sm:px-6">
                            <div class="flex flex-col items-center w-full text-gray-500 sm:hidden text-2xs">
                                <div class="whitespace-nowrap">
                                    @displayDate($transaction->transacted_at, 'M j')
                                </div>
                                <div>
                                    @displayDate($transaction->transacted_at, 'Y')
                                </div>
                            </div>
                            <div class="hidden w-full text-sm text-gray-500 sm:block whitespace-nowrap">
                                @displayDate($transaction->transacted_at, 'M j, Y')
                            </div>
                        </x-table.cell>
                        <x-table.cell class="px-2">
                            <x-community-manager::accounting.transactions.transaction-type-icon :transaction="$transaction"></x-community-manager::accounting.transactions.transaction-type-icon>
                        </x-table.cell>
                        <x-table.cell class="px-1 sm:px-6">
                            <x-community-manager::accounting.transactions.transaction-detail :transaction="$transaction"></x-community-manager::accounting.transactions.transaction-detail>
                        </x-table.cell>
                        <x-table.cell align="left" class="hidden md:table-cell">
                            <x-community-manager::accounting.transactions.transaction-type-detail :transaction="$transaction"></x-community-manager::accounting.transactions.transaction-type-detail>
                        </x-table.cell>
                        <x-table.cell align="right" class="px-1 text-xs sm:px-6 sm:text-sm md:text-sm">
                            <div class="pr-4">
                                <x-money.formatted :amount="$transaction->amount" />
                            </div>
                        </x-table.cell>
                        <x-table.cell align="center" class="px-1 sm:px-6">

                            @if(Gate::any(['edit-community-transaction', 'delete-community-transaction'], [$this->community]))
                                <x-dropdown dropdown-alignment="right" hide-arrow >
                                    <x-slot name="trigger">
                                        <x-apexicon-open.dots-vertical class="w-5 h-5 text-gray-400 cursor-pointer stroke-2 hover:text-gray-500" />
                                    </x-slot>

                                    @if(Gate::allows('edit-community-transaction', $this->community))
                                        <x-dropdown.link type="button" class="flex items-center space-x-2" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.update-transaction-modal', arguments: { transaction_id: {{ $transaction->id }} } })">
                                            <x-apexicon-open.edit class="w-5 h-5 text-gray-400 stroke-2" /><span>Edit Transaction</span>
                                        </x-dropdown.link>
                                    @endif

                                    @if(Gate::allows('delete-community-transaction', [$this->community]))
                                        <x-dropdown.link type="button" class="flex items-center space-x-2" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.delete-transaction-modal', arguments: { record_id: {{ $transaction->id }} } })">
                                            <x-apexicon-open.trash class="w-5 h-5 text-gray-400 stroke-2"/><span>Delete Transaction</span>
                                        </x-dropdown.link>
                                    @endif

                                </x-dropdown>
                            @endif
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="5">
                            <div class="flex items-center justify-center space-x-2">
                                <x-apexicon-open.scales class="w-8 h-8 text-gray-300 stroke-2"/>
                                <span class="py-8 text-xl font-medium text-gray-400">No Transactions</span>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-slot>
        </x-table>

        <x-slot name="footer">
            <div class="flex justify-between flex-1 w-full">
                {{ $this->records->onEachSide(0)->links() }}
            </div>
        </x-slot>
    </x-app.card> --}}
</div>
