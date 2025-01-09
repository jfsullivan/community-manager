<div class="w-full flex flex-col items-center pb-8">
    <div class="w-full flex justify-center bg-white border-b border-gray-200">
        <x-heading.page-heading class="w-full px-2">
            <x-slot name="avatar">
                <x-profile-photo class="h-14 w-14" :url="$this->user->profile_photo_url" :name="$this->user->name" />
            </x-slot>
            <x-slot name="label">{{ $this->user->name }}</x-slot>
            <x-slot name="description">
                <div class="flex items-center text-sm text-gray-600">
                    <x-apexicon-open.mail class="mr-1.5 h-5 w-5 shrink-0 text-gray-500" />
                    {{ $this->user->email }}
                </div>
            </x-slot>

            <x-slot name="actions">
                <div class="w-full md:w-auto flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0">
                    @can('view-member-balance', $this->community)
                        <div class="flex flex-col items-center justify-center mx-4 sm:ml-0">
                            <div class="flex text-gray-400 text-xs whitespace-nowrap">Account Balance</div>
                            @livewire('community-manager::accounting.components.member-balance', ['size' => 'lg'])
                        </div>
                    @endcan
                    @if(auth()->user()->id == $this->user->id)
                        @can('add-funds', $this->community)
                            <div class="w-full grid grid-cols-2 sm:w-auto sm:flex items-center gap-x-3">
                                <x-community-manager::accounting.add-funds-button />
                                {{-- <x-community-manager::accounting.request-payout-button /> --}}
                            </div>
                        @endcan
                    @endif
                </div>
            </x-slot>
        </x-heading.page-heading>
    </div>

    <x-app.card class="mt-0 lg:mt-8">
        <div class="w-full flex flex-col">
            <x-infolist class="divide-y divide-gray-200 last:border-b border-gray-200" searchable sortable striped :total-records="$this->records->total()" :records-shown="$this->records->count()">
                <x-slot name="title">Transaction History</x-slot>
                <x-slot name="heading">
                    <x-infolist.heading>
                        <x-infolist.heading.column sort-key="date" sort-direction="{{ $sorts['date'] ?? null }}" class="pl-2 sm:pl-4 col-span-2 justify-start">Date</x-infolist.heading.column>
                        <x-infolist.heading.column sort-key="transaction" sort-direction="{{ $sorts['transaction'] ?? null }}" class="col-span-7 justify-start lg:pl-7">Transaction</x-infolist.heading.column>
                        <div class="col-span-3 w-full flex flex-col-reverse lg:grid lg:grid-cols-2 lg:gap-x-2 items-center justify-end">
                            <x-infolist.heading.column sort-key="type" sort-direction="{{ $sorts['type'] ?? null }}" class="hidden lg:flex justify-end lg:justify-start">Type</x-infolist.heading.column>
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
                            <x-infolist.item.column class="pl-2 sm:pl-4 col-span-2 justify-start">
                                <div class="w-full flex flex-col items-start justify-start lg:hidden text-gray-500">
                                    <p class="whitespace-nowrap text-xs sm:text-sm font-medium leading-5 sm:leading-6">
                                        @displayDate($transaction->transacted_at, 'M j')
                                    </p>
                                    <p class="text-2xs sm:text-xs md:text-sm leading-4 sm:leading-5">
                                        @displayDate($transaction->transacted_at, 'Y')
                                    </p>
                                </div>
                                <div class="w-full hidden lg:block text-sm whitespace-nowrap text-gray-500">
                                    @displayDate($transaction->transacted_at, 'M j, Y')
                                </div>
                            </x-infolist.item.column>
                            <x-infolist.item.column class="col-span-7 justify-start lg:space-x-4">
                                <div class="hidden lg:inline">
                                    <x-community-manager::accounting.transactions.transaction-type-icon :type="$transaction->type->slug" />
                                </div>
                                <x-community-manager::accounting.transactions.transaction-detail :transaction="$transaction" class="text-gray-900 font-medium" />
                            </x-infolist.item.column>
                            <div class="col-span-3 w-full flex flex-col-reverse lg:grid lg:grid-cols-2 lg:gap-x-2 items-center justify-end">
                                <x-infolist.item.column class="flex justify-end lg:justify-start text-sm">
                                    <x-community-manager::accounting.transactions.transaction-type-detail :transaction="$transaction" />
                                </x-infolist.item.column>

                                <x-infolist.item.column class="flex justify-end text-xs sm:text-sm">
                                    <x-money :amount="$transaction->amount" formatted />
                                </x-infolist.item.column>
                            </div>
                        </x-slot>

                        <x-slot name="actions">
                            <div class="w-10 sm:w-16 flex justify-center items-center">
                                @if(Gate::any(['edit-community-transaction', 'delete-community-transaction'], [$this->community]))
                                    <x-dropdown dropdown-alignment="right" hide-arrow >
                                        <x-slot name="trigger">
                                            <x-apexicon-open.dots-vertical class="h-5 w-5 text-gray-400 cursor-pointer hover:text-gray-500 stroke-2" />
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
                    <x-empty-state class="w-full flex py-8">
                        <x-slot name="icon">
                            <x-icons.featured-double inner-color="bg-primary-100" class="bg-primary-50">
                                <x-apexicon-open.coins-swap class="w-5 h-5 text-primary-600 stroke-2"/>
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
            </x-infolist>
        </div>

        <x-slot name="footer">
            <div class="w-full flex flex-1 justify-between">
                {{ $this->records->links('ui-kit::components.pagination.standard.index') }}
            </div>
        </x-slot>
    </x-app.card>
</div>
