<div class="flex flex-col items-center w-full pb-8">

    <x-slot name="breadcrumbs">
        <x-apex::breadcrumbs.item href="{{ route('community.admin.accounting.index') }}">Accounting</x-apex::breadcrumbs.item>
        <x-apex::breadcrumbs.item href="{{ route('community.admin.accounting.member.balances') }}">Member Balances</x-apex::breadcrumbs.item>
        <x-apex::breadcrumbs.item>{{ $this->user->full_name }} Transactions</x-apex::breadcrumbs.item>
    </x-slot>

    <div class="flex justify-center w-full bg-white border-b border-gray-200">
        <div class="w-full flex flex-col md:flex-row md:justify-between space-y-2 md:space-y-0 py-5 px-2 md:px-4 bg-white">
            <div class="w-full flex items-center space-x-4">
                <x-profile-photo class="h-14 w-14" :url="$this->user->profile_photo_url" :name="$this->user->full_name" />
                <div class="flex flex-col">
                    <x-apex::heading size="xl" class="mb-0! font-semibold!">{{ $this->user->full_name }}</x-apex::heading>
                    <div class="flex items-center text-sm text-gray-600">
                        <flux:icon name="apex-ui.mail" class="mr-1.5 h-5 w-5 shrink-0 text-gray-500" />
                        {{ $this->user->email }}
                    </div>
                </div>
            </div>

            <div class="flex items-center">
                @can('view-member-balance', $this->community)
                    <div class="flex flex-col items-center justify-center mx-4 sm:ml-0">
                        <div class="flex text-xs text-gray-400 whitespace-nowrap">Account Balance</div>
                        <livewire:community-manager.accounting.components.member-balance :user_id="$this->user->id" size="lg" :selectable="false" />
                    </div>
                @endcan
                @if(Gate::allows('create-community-transaction', $this->community))
                    <x-apex::button size="sm" icon="apex-ui.plus" wire:click="$dispatch('open-create-transaction', { user_id: {{ $this->user->id }} })">
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
                <x-apex::menu.item icon="apex-ui.trash" wire:click="$dispatch('open-delete-transaction', { records: $wire.selected })">Delete Selected Transactions</x-apex::menu.item>
            </x-slot:bulkActions>

            <x-slot:header actions-variant="icon">
                <x-apex::grid.header.column class="justify-start col-span-3" sortable sort-key="date" :sort-data="$sorts">Date</x-apex::grid.header.column>
                <x-apex::grid.header.column class="justify-start col-span-9 lg:pl-7" sortable sort-key="transaction" :sort-data="$sorts">Transaction</x-apex::grid.header.column>
                <div class="flex flex-col-reverse items-center justify-end w-full col-span-4 lg:grid lg:grid-cols-2 lg:gap-x-2">
                    <x-apex::grid.header.column class="justify-end hidden lg:flex lg:justify-start" sortable sort-key="type" :sort-data="$sorts">Type</x-apex::grid.header.column>
                    <x-apex::grid.header.column class="flex justify-end" sortable sort-key="amount" :sort-data="$sorts">Amount</x-apex::grid.header.column>
                </div>
            </x-slot:header>

            @forelse ($this->records as $transaction)
                <x-apex::grid.item wire:key="transaction-{{ $transaction->id }}" wire:model="selected">
                    <x-apex::grid.item.column class="justify-start col-span-3">
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
                    </x-apex::grid.item.column>
                    <x-apex::grid.item.column class="justify-start col-span-9 lg:space-x-4">
                        <div class="hidden lg:inline">
                            <x-community-manager::accounting.transactions.transaction-type-icon :type="$transaction->type->slug" />
                        </div>
                        <x-community-manager::accounting.transactions.transaction-detail :transaction="$transaction" class="font-medium text-gray-900" />
                    </x-apex::grid.item.column>
                    <div class="flex flex-col-reverse items-center justify-end w-full col-span-4 lg:grid lg:grid-cols-2 lg:gap-x-2">
                        <x-apex::grid.item.column class="flex justify-end text-sm lg:justify-start">
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
                <div class="flex justify-between flex-1 w-full">
                    {{ $this->records->links() }}
                </div>
            </x-slot>
        </x-apex::grid>
    </div>

    <livewire:community-manager.accounting.modals.create-transaction-modal />
    <livewire:community-manager.accounting.modals.update-transaction-modal />
    <livewire:community-manager.accounting.modals.delete-transaction-modal />
</div>
