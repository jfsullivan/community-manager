<x-apex::modal.form
    name="add-funds"
    width="w-full sm:max-w-md"
    heading="Payment Methods"
    loading-target="openModal"
    :bordered="false"
>
    <x-slot name="icon">
        <x-icons.featured-double class="bg-green-50 dark:bg-green-500/10" inner-color="bg-green-100 dark:bg-green-500/15">
            <flux:icon name="apex-ui.credit-card" class="w-6 h-6 text-green-600 dark:text-green-400 stroke-2" />
        </x-icons.featured-double>
    </x-slot>

    @if($show)
        <div class="w-full text-sm text-gray-600 dark:text-zinc-300 px-2 sm:px-0">
            <span>The following payment methods are available for adding funds to your account for</span>
            <span class="font-semibold">{{ $this->community?->name }}</span>.
        </div>

        <div class="w-full mt-4 flex flex-col space-y-4">
            @forelse($this->paymentMethods as $paymentMethod)
                <div class="w-full flex flex-col items-start px-4 sm:px-0">
                    <div class="text-sm text-left font-semibold text-gray-900 dark:text-zinc-100">{{ $paymentMethod->name }}</div>
                    <div class="text-sm text-left text-gray-600 dark:text-zinc-300">{!! data_get($paymentMethod, 'pivot.value', '') !!}</div>
                </div>
            @empty
                <div class="w-full flex flex-col items-start">
                    <div class="text-sm text-left font-semibold">No payment methods available.</div>
                </div>
            @endforelse
        </div>
    @endif

    <x-slot name="actions">
        <flux:button variant="primary" wire:click="closeModal">{{ __('Close') }}</flux:button>
    </x-slot>
</x-apex::modal.form>
