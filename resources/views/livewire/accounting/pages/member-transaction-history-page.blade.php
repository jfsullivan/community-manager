<div class="w-full flex flex-col items-center pb-8">
    <div class="w-full flex justify-center bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-700">
        {{-- Same centered width as the community home and pool pages. Phones:
             name and email, then the balance (label over amount, centered
             like the button below) and full-width actions. Wider: the balance and
             actions sit to the right of the name. --}}
        <div class="w-full max-w-7xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-4 py-5 px-4 bg-white dark:bg-zinc-900">
            <div class="min-w-0 flex items-center gap-4">
                <x-profile-photo class="h-14 w-14 shrink-0" :url="$this->user->profile_photo_url" :name="$this->user->name" />
                <div class="min-w-0 flex flex-col">
                    <x-apex::heading size="xl" class="mb-0! font-semibold! truncate">{{ $this->user->name }}</x-apex::heading>
                    <div class="min-w-0 flex items-center text-sm text-gray-600 dark:text-zinc-300">
                        <flux:icon name="apex-ui.mail" class="mr-1.5 h-5 w-5 shrink-0 text-gray-500 dark:text-zinc-400" />
                        <span class="truncate">{{ $this->user->email }}</span>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-auto flex flex-col md:flex-row md:items-center gap-4 md:gap-6">
                @can('view-member-balance', $this->community)
                    <div class="flex flex-col items-center">
                        <div class="text-xs text-gray-500 dark:text-zinc-400 whitespace-nowrap">Account Balance</div>
                        {{-- This member's balance (not the viewer's), shown as text: it
                             would only link back to this page. --}}
                        @livewire('community-manager.accounting.components.member-balance', ['user_id' => $this->user->id, 'community_id' => $this->community->id, 'selectable' => false, 'class' => 'text-lg md:text-base'], key('member-balance-'.$this->user->id))
                    </div>
                @endcan
                @if(auth()->user()->id == $this->user->id)
                    <x-community-manager::accounting.member-balance-actions :community="$this->community" :user="$this->user" />
                @endif
            </div>
        </div>
    </div>

    {{-- Phones: the edge-to-edge card sits right under the header. --}}
    <div class="flex flex-col w-full max-w-7xl mx-auto pb-6 sm:pt-6 sm:px-2 md:px-4 md:py-8">
        <x-apex::grid card bleed striped searchable mobile-toolbar class="w-full grid-cols-16">
            <x-slot name="heading">Transaction History</x-slot>

            {{-- Type up front; pool and dates in the Filters menu. The Pool
                 filter shows only when the app ties transactions to pools. --}}
            <x-slot:filterRow>
                <x-apex::filter-row :active="$this->menuFilterCount()" :primary-active="$this->transactionTypeSlugs() !== []" clear-action="clearAllFilters">
                    <x-slot:primary>
                        <x-apex::input.select multiple label="Type" wire:model.live="transactionTypeFilter" placeholder="Any type">
                            @foreach ($this->transactionTypes as $type)
                                <x-apex::input.select.option value="{{ $type->slug }}">{{ $type->name }}</x-apex::input.select.option>
                            @endforeach
                        </x-apex::input.select>
                    </x-slot:primary>

                    @if ($this->poolOptions !== [])
                        {{-- Name, with the season or tournament underneath: many pools
                             share a name across years. Search matches either. --}}
                        <x-apex::input.select nullable searchable label="Pool" wire:model="poolFilter" placeholder="Any pool">
                            @foreach ($this->poolOptions as $poolId => $pool)
                                {{-- The season is Flux's option description: small, under the
                                     name in the list, and left out of the closed field. --}}
                                <x-apex::input.select.option value="{{ $poolId }}" :description="$pool['detail'] ?? null">
                                    <span class="block truncate">{{ $pool['name'] }}</span>
                                </x-apex::input.select.option>
                            @endforeach
                        </x-apex::input.select>
                    @endif

                    <x-apex::input.date-picker
                        mode="range"
                        label="Dates"
                        wire:model="dateRange"
                        presets="last7Days last30Days thisMonth lastMonth thisYear lastYear"
                        placeholder="Any dates"
                        clearable
                    />
                </x-apex::filter-row>
            </x-slot:filterRow>

            {{-- Read-only history: editing and deleting live on the community admin Transactions page. --}}
            <x-slot:header>
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
                        <div class="w-full flex flex-col items-start justify-start lg:hidden text-gray-500 dark:text-zinc-400">
                            <p class="whitespace-nowrap text-xs sm:text-sm font-medium leading-5 sm:leading-6">
                                @displayDate($transaction->transacted_at, 'M j')
                            </p>
                            <p class="text-2xs sm:text-xs md:text-sm leading-4 sm:leading-5">
                                @displayDate($transaction->transacted_at, 'Y')
                            </p>
                        </div>
                        <div class="w-full hidden lg:block text-sm whitespace-nowrap text-gray-500 dark:text-zinc-400">
                            @displayDate($transaction->transacted_at, 'M j, Y')
                        </div>
                    </x-apex::grid.item.column>
                    <x-apex::grid.item.column class="col-span-9 justify-start lg:space-x-4">
                        <div class="hidden lg:inline">
                            <x-community-manager::accounting.transactions.transaction-type-icon :type="$transaction->type->slug" />
                        </div>
                        <x-community-manager::accounting.transactions.transaction-detail :transaction="$transaction" class="text-gray-900 dark:text-zinc-100 font-medium" />
                    </x-apex::grid.item.column>
                    <div class="col-span-4 w-full flex flex-col-reverse lg:grid lg:grid-cols-2 lg:gap-x-2 items-center justify-end">
                        <x-apex::grid.item.column class="flex justify-end lg:justify-start text-sm">
                            <x-community-manager::accounting.transactions.transaction-type-detail :transaction="$transaction" />
                        </x-apex::grid.item.column>

                        <x-apex::grid.item.column class="flex justify-end text-xs sm:text-sm">
                            <x-money :amount="$transaction->amount" formatted />
                        </x-apex::grid.item.column>
                    </div>

                </x-apex::grid.item>
            @empty
                <x-apex::grid.item :selectable="false">
                    <x-apex::grid.item.column class="col-span-full justify-center">
                        @if (filled($this->searchFilter) || $this->transactionTypeSlugs() !== [] || $this->menuFilterCount() > 0)
                            <x-apex::empty-state icon="apex-ui.search" heading="No transactions found" subheading="No transactions match these filters.">
                                <x-slot:actions>
                                    <x-apex::button variant="outline" wire:click="clearAllFilters">Clear filters</x-apex::button>
                                </x-slot:actions>
                            </x-apex::empty-state>
                        @else
                            <x-apex::empty-state icon="apex-ui.coins-swap" heading="No transactions yet">
                                <x-slot:subheading>{{ $this->user->full_name }} doesn't have any transactions yet.</x-slot:subheading>
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

</div>
