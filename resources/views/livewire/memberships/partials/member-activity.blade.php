{{-- Community activity panel for the member details page: balance + recent transactions. --}}
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

    <div class="p-4">
        <dl class="mb-4 text-sm">
            <dt class="text-zinc-500 dark:text-zinc-400">Balance</dt>
            <dd class="text-lg font-semibold"><x-money :amount="$this->memberBalance" formatted /></dd>
        </dl>

        <x-apex::heading size="sm" class="mb-2!">Recent Transactions</x-apex::heading>

        @forelse ($this->recentTransactions as $transaction)
            <div class="flex items-center justify-between border-t border-zinc-100 py-2 text-sm dark:border-zinc-800">
                <div class="flex flex-col">
                    <span>{{ $transaction->type->name ?? 'Transaction' }}</span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $transaction->transacted_at?->format('M j, Y') }}{{ $transaction->description ? ' — '.$transaction->description : '' }}</span>
                </div>
                <x-money :amount="$transaction->amount" formatted />
            </div>
        @empty
            <x-apex::text>No transactions recorded for this member.</x-apex::text>
        @endforelse
    </div>
</x-apex::card>
