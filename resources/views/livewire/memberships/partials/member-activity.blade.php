{{-- Community activity panel for the member details page: balance + recent
     transactions as a compact grid (date, transaction, amount), scrolling after
     about five rows. All Transactions opens the full history. --}}
<x-apex::card bleed heading="Account">
    <x-slot:actions>
        <x-apex::button
            size="xs"
            variant="ghost"
            :href="route('community.admin.accounting.member.transactions', ['user_id' => $member->id])"
        >
            {{ __('All Transactions') }}
        </x-apex::button>
    </x-slot:actions>

    <dl class="p-4 text-sm">
        <dt class="text-zinc-500 dark:text-zinc-400">Balance</dt>
        <dd class="text-lg font-semibold"><x-money :amount="$this->memberBalance" formatted /></dd>
    </dl>

    <div class="border-t border-zinc-100 dark:border-zinc-800">
        <div class="grid grid-cols-12 gap-2 border-b border-zinc-800/10 px-4 py-3 text-2xs font-medium text-zinc-600 sm:text-xs dark:border-white/10 dark:text-white">
            <div class="col-span-3">Date</div>
            <div class="col-span-6">Recent transaction</div>
            <div class="col-span-3 text-right">Amount</div>
        </div>

        <div class="max-h-72 overflow-y-auto">
            @forelse ($this->recentTransactions as $transaction)
                <div class="grid grid-cols-12 items-center gap-2 border-b border-zinc-100 px-4 py-2.5 text-sm last:border-b-0 dark:border-zinc-800">
                    <div class="col-span-3 text-zinc-500 dark:text-zinc-400">{{ $transaction->transacted_at?->format('M j, Y') }}</div>
                    <div class="col-span-6 flex min-w-0 flex-col">
                        <span class="truncate text-zinc-900 dark:text-zinc-100">{{ $transaction->type->name ?? 'Transaction' }}</span>
                        @if ($transaction->description)
                            <span class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $transaction->description }}</span>
                        @endif
                    </div>
                    <div class="col-span-3 text-right"><x-money :amount="$transaction->amount" formatted /></div>
                </div>
            @empty
                <div class="px-4 py-3">
                    <x-apex::text>No transactions recorded for this member.</x-apex::text>
                </div>
            @endforelse
        </div>
    </div>
</x-apex::card>
