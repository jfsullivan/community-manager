<div class="w-full flex flex-col items-center pb-8">
    <div class="w-full flex justify-center bg-white border-b border-gray-200">
        <div class="w-full flex flex-col md:flex-row md:justify-between space-y-2 md:space-y-0 py-5 px-2 md:px-4 bg-white">
            <div class="w-full flex items-center space-x-4">
                <x-profile-photo class="h-14 w-14" :url="$this->user->profile_photo_url" :name="$this->user->name" />
                <div class="flex flex-col">
                    <x-apex::heading size="xl" class="mb-0! font-semibold!">{{ $this->user->name }}</x-apex::heading>
                    <div class="flex items-center text-sm text-gray-600">
                        <flux:icon name="apex-ui.mail" class="mr-1.5 h-5 w-5 shrink-0 text-gray-500" />
                        {{ $this->user->email }}
                    </div>
                </div>
            </div>

            <div class="w-full md:w-auto flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0">
                @can('view-member-balance', $this->community)
                    <div class="flex flex-col items-center justify-center mx-4 sm:ml-0">
                        <div class="flex text-gray-400 text-xs whitespace-nowrap">Account Balance</div>
                        @livewire('community-manager.accounting.components.member-balance', ['size' => 'lg'])
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
        </div>
    </div>

    <x-app.card class="mt-0 lg:mt-8">
        <div class="w-full flex flex-col">
            <x-apex::grid striped searchable class="w-full grid-cols-16">
                <x-slot name="heading">Transaction History</x-slot>

                <x-slot:header actions-variant="icon">
                    <x-apex::grid.header.column class="pl-2 sm:pl-4 col-span-3 justify-start" sortable sort-key="date" :sort-data="$sorts">Date</x-apex::grid.header.column>
                    <x-apex::grid.header.column class="col-span-9 justify-start lg:pl-7" sortable sort-key="transaction" :sort-data="$sorts">Transaction</x-apex::grid.header.column>
                    <div class="col-span-4 w-full flex flex-col-reverse lg:grid lg:grid-cols-2 lg:gap-x-2 items-center justify-end">
                        <x-apex::grid.header.column class="hidden lg:flex justify-end lg:justify-start" sortable sort-key="type" :sort-data="$sorts">Type</x-apex::grid.header.column>
                        <x-apex::grid.header.column class="flex justify-end" sortable sort-key="amount" :sort-data="$sorts">Amount</x-apex::grid.header.column>
                    </div>
                </x-slot:header>

                @forelse ($this->records as $transaction)
                    <x-apex::grid.item wire:key="transaction-{{ $transaction->id }}">
                        <x-apex::grid.item.column class="pl-2 sm:pl-4 col-span-3 justify-start">
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
                        </x-apex::grid.item.column>
                        <x-apex::grid.item.column class="col-span-9 justify-start lg:space-x-4">
                            <div class="hidden lg:inline">
                                <x-community-manager::accounting.transactions.transaction-type-icon :type="$transaction->type->slug" />
                            </div>
                            <x-community-manager::accounting.transactions.transaction-detail :transaction="$transaction" class="text-gray-900 font-medium" />
                        </x-apex::grid.item.column>
                        <div class="col-span-4 w-full flex flex-col-reverse lg:grid lg:grid-cols-2 lg:gap-x-2 items-center justify-end">
                            <x-apex::grid.item.column class="flex justify-end lg:justify-start text-sm">
                                <x-community-manager::accounting.transactions.transaction-type-detail :transaction="$transaction" />
                            </x-apex::grid.item.column>

                            <x-apex::grid.item.column class="flex justify-end text-xs sm:text-sm">
                                <x-money :amount="$transaction->amount" formatted />
                            </x-apex::grid.item.column>
                        </div>

                        <x-slot:actions>
                            @php
                                $canManageTransactions = Gate::any(['edit-community-transaction', 'delete-community-transaction'], [$this->community]);
                            @endphp
                            <x-apex::grid.item.column.actions.dropdown :disabled="! $canManageTransactions">
                                @if(Gate::allows('edit-community-transaction', $this->community))
                                    <x-apex::menu.item icon="apex-ui.edit" wire:click="$dispatch('open-update-transaction', { id: {{ $transaction->id }} })">Edit Transaction</x-apex::menu.item>
                                @endif

                                @if(Gate::allows('delete-community-transaction', [$this->community]))
                                    <x-apex::menu.item icon="apex-ui.trash" wire:click="$dispatch('open-delete-transaction', { id: {{ $transaction->id }} })">Delete Transaction</x-apex::menu.item>
                                @endif
                            </x-apex::grid.item.column.actions.dropdown>
                        </x-slot:actions>
                    </x-apex::grid.item>
                @empty
                    <x-apex::grid.item :selectable="false">
                        <x-apex::grid.item.column class="col-span-full justify-center">
                            <x-apex::empty-state icon="apex-ui.coins-swap" heading="No transactions yet" class="mt-6 mb-6">
                                <x-slot:subheading>
                                    {{ $this->user->full_name }} doesn't have any transactions that meet that criteria.
                                </x-slot:subheading>
                            </x-apex::empty-state>
                        </x-apex::grid.item.column>
                    </x-apex::grid.item>
                @endforelse

                <x-slot name="footer">
                    <div class="w-full flex flex-1 justify-between">
                        {{ $this->records->links() }}
                    </div>
                </x-slot>
            </x-apex::grid>
        </div>
    </x-app.card>

    <livewire:community-manager.accounting.modals.update-transaction-modal />
    <livewire:community-manager.accounting.modals.delete-transaction-modal />
</div>
