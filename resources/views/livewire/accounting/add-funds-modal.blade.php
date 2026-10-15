<x-apex::modal.form
    name="add-funds"
    width="w-full sm:max-w-md"
    heading="Payment Methods"
    loading-target="openModal"
    :bordered="false"
    actions-layout="end"
>
    <x-slot:icon>
        <x-apex::featured-icon name="apex-ui.credit-card" />
    </x-slot:icon>

    @if($show)
        <div class="w-full text-sm text-gray-600 dark:text-zinc-300">
            <span>The following payment methods are available for adding funds to your account for</span>
            <span class="font-semibold">{{ $this->community?->name }}</span>.
        </div>

        <div class="w-full mt-4 flex flex-col space-y-4">
            @forelse($this->paymentMethods as $paymentMethod)
                <div class="w-full flex flex-col items-start">
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
        <flux:modal.close>
            <x-apex::button variant="primary">{{ __('Close') }}</x-apex::button>
        </flux:modal.close>
    </x-slot>
</x-apex::modal.form>
