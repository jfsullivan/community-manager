<div class="flex flex-col items-center w-full pb-8">

    <x-breadcrumbs class="bg-gray-100">
        <x-breadcrumbs.item url="{{ route('community.admin.index') }}"><x-apexicon-open.speedometer class="flex-shrink-0 w-4 h-4 stroke-2 sm:h-5 sm:w-5" /></x-breadcrumbs.item>
        <x-breadcrumbs.dividers.chevron />
        <x-breadcrumbs.item url="{{ route('community.admin.accounting.index') }}">Accounting</x-breadcrumbs.item>
        <x-breadcrumbs.dividers.chevron />
        <x-breadcrumbs.item>Transactions</x-breadcrumbs.item>
    </x-breadcrumbs>

    <div class="flex justify-center w-full bg-white border-b border-gray-200">
        <x-heading.page-heading class="w-full px-2">
            <x-slot name="label">Community Transactions</x-slot>
            <x-slot name="description">Manage all transactions for your community.</x-slot>

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
        <x-infolist searchable selectable sortable striped bordered-y paginated>
            <x-slot name="heading">
                <x-infolist.heading>
                    <x-infolist.heading.column sort-key="date" sort-direction="{{ $sorts['date'] ?? null }}" class="justify-start col-span-2">Date</x-infolist.heading.column>
                    <x-infolist.heading.column sort-key="member" sort-direction="{{ $sorts['member'] ?? null }}" class="justify-start col-span-2">Member</x-infolist.heading.column>
                    <x-infolist.heading.column sort-key="transaction" sort-direction="{{ $sorts['transaction'] ?? null }}" class="justify-start col-span-5 lg:pl-7">Transaction</x-infolist.heading.column>
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
                        <x-infolist.item.column class="justify-start col-span-2 font-medium text-gray-900">
                            {{ $transaction->user->name }}
                        </x-infolist.item.column>
                        <x-infolist.item.column class="justify-start col-span-5 lg:space-x-4">
                            <div class="hidden lg:inline">
                                <x-community-manager::accounting.transactions.transaction-type-icon :type="$transaction->type->slug" />
                            </div>
                            <x-community-manager::accounting.transactions.transaction-detail :transaction="$transaction" class="text-gray-500" />
                        </x-infolist.item.column>
                        <div class="flex flex-col-reverse items-center justify-end w-full col-span-3 lg:grid lg:grid-cols-2 lg:gap-x-2">
                            <x-infolist.item.column class="flex justify-end text-sm lg:justify-start">
                                <x-community-manager::accounting.transactions.transaction-type-detail :transaction="$transaction"></x-community-manager::accounting.transactions.transaction-type-detail>
                            </x-infolist.item.column>

                            <x-infolist.item.column class="flex justify-end text-xs sm:text-sm">
                                <x-money :amount="$transaction->amount" formatted />
                            </x-infolist.item.column>
                        </div>
                    </x-slot>

                    <x-slot name="actions">
                        <div class="flex items-center justify-center w-10 sm:w-16">
                            @if(Gate::any(['edit-community-transaction', 'delete-community-transaction'], [$this->community]))
                                <x-menu>
                                    <x-menu.button>
                                        <x-apexicon-open.dots-vertical class="w-5 h-5 text-gray-400 cursor-pointer stroke-2 hover:text-gray-500" />
                                    </x-menu.button>

                                    <x-menu.items>
                                        @if(Gate::allows('edit-community-transaction', $this->community))
                                            <x-menu.close>
                                                <x-menu.item class="flex items-center space-x-2" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.update-transaction-modal', arguments: { transaction_id: {{ $transaction->id }} } })">
                                                    <x-apexicon-open.edit class="w-5 h-5 text-gray-400 stroke-2" /><span>Edit Transaction</span>
                                                </x-menu.item>
                                            </x-menu.close>
                                        @endif

                                        @if(Gate::allows('delete-community-transaction', [$this->community]))
                                            <x-menu.close>
                                                <x-menu.item disabled class="flex items-center space-x-2" wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.delete-transaction-modal', arguments: { record_id: {{ $transaction->id }} } })">
                                                    <x-apexicon-open.trash class="w-5 h-5 text-gray-400 stroke-2"/><span>Delete Transaction</span>
                                                </x-menu.item>
                                            </x-menu.close>
                                        @endif
                                    </x-menu.items>
                                </x-menu>



                                {{-- <x-dropdown dropdown-alignment="right" hide-arrow >
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

                                </x-dropdown> --}}
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
                        There aren't any transactions that meet that criteria.
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
                <div class="flex justify-end flex-1 w-full">
                    {{ $this->records->links('ui-kit::components.pagination.simple.index') }}
                    {{-- @if($this->records->total())
                            {{ $this->records->onEachSide(0)->links('ui-kit::components.pagination.standard.index') }}
                    @else

                    @endif --}}
                </div>
            </x-slot>

        </x-infolist>
    </div>
</div>
