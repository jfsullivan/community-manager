{{-- Low-balance reminder. Only rendered when the member is below their
     community's threshold and hasn't been reminded this week (gated in
     jfsullivan\CommunityManager\View\Components\LowBalanceReminder). "See how
     to add funds" opens the community's Add Funds dialog. --}}
@if ($show)
    @php($format = fn (int $cents) => ($cents < 0 ? '-' : '').'$'.number_format(abs($cents) / 100, 2))

    <div
        x-data="{ open: true }"
        x-show="open"
        x-cloak
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-60 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="low-balance-headline"
    >
        <div @click="open = false" class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm"></div>

        <div x-show="open" x-transition
            class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white p-6 text-center shadow-2xl ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700">
            <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-400/15">
                <flux:icon name="apex-ui.credit-card" class="size-7 text-amber-600 dark:text-amber-400" />
            </div>

            <h2 id="low-balance-headline" class="mt-4 text-xl font-bold text-zinc-900 dark:text-white">
                Time to add funds
            </h2>

            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                Your balance in <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $community->name }}</span> is
                <span @class([
                    'font-semibold tabular-nums',
                    'text-red-600 dark:text-red-400' => $balanceCents < 0,
                    'text-zinc-900 dark:text-zinc-100' => $balanceCents >= 0,
                ])>{{ $format($balanceCents) }}</span>,
                below the community's {{ $format($thresholdCents) }} minimum. Add funds so your entry fees are covered.
            </p>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-center">
                <x-apex::button variant="ghost" x-on:click="open = false">Not now</x-apex::button>
                <x-apex::button variant="primary" icon="apex-ui.credit-card-upload"
                    x-on:click="open = false; Livewire.dispatch('open-add-funds', { community_id: {{ $community->id }} })">
                    See how to add funds
                </x-apex::button>
            </div>
        </div>
    </div>
@endif
