<div class="flex flex-col items-center w-full pb-8">

    <x-slot name="breadcrumbs">
        <x-apex::breadcrumbs.item href="{{ route('community.admin.accounting.index') }}">Accounting</x-apex::breadcrumbs.item>
        <x-apex::breadcrumbs.item>Transactions</x-apex::breadcrumbs.item>
    </x-slot>

    <x-apex::section-header
        heading="Community Transactions"
        subheading="Manage all transactions for your community."
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
            <x-slot:heading>All transactions</x-slot:heading>
            <x-slot:subheading>Every deposit, withdrawal, fee and payout in the community.</x-slot:subheading>

            <x-slot:filterRow>
                <x-apex::filter-row :active="count(array_filter([$methodFilter, $periodFilter]))" :primary-active="filled($transactionTypeFilter)" clear-action="clearAllFilters">
                    <x-slot:primary>
                        <x-apex::input.select nullable label="Type" wire:model.live="transactionTypeFilter" placeholder="Any type">
                            @foreach ($this->transactionTypes as $type)
                                <x-apex::input.select.option value="{{ $type->id }}">{{ $type->name }}</x-apex::input.select.option>
                            @endforeach
                        </x-apex::input.select>
                    </x-slot:primary>

                    <x-apex::input.select nullable label="Method" wire:model.live="methodFilter" placeholder="Any method">
                        @foreach (\jfsullivan\CommunityManager\Enums\TransactionMethod::options() as $option)
                            <x-apex::input.select.option value="{{ $option['value'] }}">{{ $option['label'] }}</x-apex::input.select.option>
                        @endforeach
                    </x-apex::input.select>

                    <x-apex::input.select nullable label="When" wire:model.live="periodFilter" placeholder="Any time">
                        @foreach ($this->periodOptions() as $value => $label)
                            <x-apex::input.select.option value="{{ $value }}">{{ $label }}</x-apex::input.select.option>
                        @endforeach
                    </x-apex::input.select>
                </x-apex::filter-row>
            </x-slot:filterRow>

            @if (Gate::allows('delete-community-transaction', [$this->community]))
                <x-slot:bulkActions>
                    <x-apex::menu.item icon="apex-ui.trash" wire:click="$dispatch('open-delete-transaction', { records: $wire.selected })">Delete Selected Transactions</x-apex::menu.item>
                </x-slot:bulkActions>
            @endif

            <x-slot:header actions-variant="icon">
                <x-apex::grid.header.column class="justify-start col-span-3" sortable sort-key="date" :sort-data="$sorts">Date</x-apex::grid.header.column>
                <x-apex::grid.header.column class="justify-start col-span-3" sortable sort-key="member" :sort-data="$sorts">Member</x-apex::grid.header.column>
                <x-apex::grid.header.column class="justify-start col-span-6 lg:pl-7" sortable sort-key="transaction" :sort-data="$sorts">Transaction</x-apex::grid.header.column>
                <div class="flex flex-col-reverse items-center justify-end w-full col-span-4 lg:grid lg:grid-cols-2 lg:gap-x-2">
                    <x-apex::grid.header.column class="justify-end hidden lg:flex lg:justify-start" sortable sort-key="type" :sort-data="$sorts">Type</x-apex::grid.header.column>
                    <x-apex::grid.header.column class="flex justify-end" sortable sort-key="amount" :sort-data="$sorts">Amount</x-apex::grid.header.column>
                </div>
            </x-slot:header>

            @forelse ($this->records as $transaction)
                <x-apex::grid.item wire:key="transaction-{{ $transaction->id }}" wire:model="selected">
                    <x-apex::grid.item.column class="justify-start col-span-3">
                        <div class="flex flex-col items-start justify-start w-full text-gray-500 dark:text-zinc-400 lg:hidden">
                            <p class="text-xs font-medium leading-5 whitespace-nowrap sm:text-sm sm:leading-6">
                                @displayDate($transaction->transacted_at, 'M j')
                            </p>
                            <p class="leading-4 text-2xs sm:text-xs md:text-sm sm:leading-5">
                                @displayDate($transaction->transacted_at, 'Y')
                            </p>
                        </div>
                        <div class="hidden w-full text-sm text-gray-500 dark:text-zinc-400 lg:block whitespace-nowrap">
                            @displayDate($transaction->transacted_at, 'M j, Y')
                        </div>
                    </x-apex::grid.item.column>
                    <x-apex::grid.item.column class="justify-start col-span-3 font-medium text-gray-900 dark:text-zinc-100">
                        {{ $transaction->user->name }}
                    </x-apex::grid.item.column>
                    <x-apex::grid.item.column class="justify-start col-span-6 lg:space-x-4">
                        <div class="hidden lg:inline">
                            <x-community-manager::accounting.transactions.transaction-type-icon :type="$transaction->type->slug" />
                        </div>
                        <x-community-manager::accounting.transactions.transaction-detail :transaction="$transaction" class="text-gray-500 dark:text-zinc-400" />
                    </x-apex::grid.item.column>
                    <div class="flex flex-col-reverse items-center justify-end w-full col-span-4 lg:grid lg:grid-cols-2 lg:gap-x-2">
                        <x-apex::grid.item.column class="flex justify-end text-sm lg:justify-start">
                            <x-community-manager::accounting.transactions.transaction-type-detail :transaction="$transaction"></x-community-manager::accounting.transactions.transaction-type-detail>
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
                        @if (filled($this->searchFilter))
                            <x-apex::empty-state icon="apex-ui.search" heading="No transactions found" subheading="We couldn't find any transactions that meet that criteria.">
                                <x-slot:actions>
                                    <x-apex::button variant="outline" wire:click="clearSearch">Clear search</x-apex::button>
                                </x-slot:actions>
                            </x-apex::empty-state>
                        @else
                            <x-apex::empty-state icon="apex-ui.coins-swap" heading="No transactions yet">
                                <x-slot:subheading>No transactions have been recorded yet.</x-slot:subheading>
                            </x-apex::empty-state>
                        @endif
                    </x-apex::grid.item.column>
                </x-apex::grid.item>
            @endforelse

            <x-slot name="footer">
                <x-apex::grid.load-more :records="$this->records" class="mt-2" />
            </x-slot>

        </x-apex::grid>
    </div>

    <livewire:community-manager.accounting.modals.create-transaction-modal />
    <livewire:community-manager.accounting.modals.update-transaction-modal />
    <livewire:community-manager.accounting.modals.delete-transaction-modal />
</div>
