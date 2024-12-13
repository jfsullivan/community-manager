<x-modal.livewire id="payment-methods" :centered1="false">
    <x-slot name="icon">
        <x-icons.featured-double class="bg-green-50" inner-color="bg-green-100">
            <x-apexicon-open.credit-card class="w-6 h-6 text-green-600 stroke-2" />
        </x-icons.featured-double>
    </x-slot>

    <x-slot name="title">Payment Methods</x-slot>

    <x-slot name="description" class="inline px-2 sm:px-0">
        <span>The following payment methods are available for adding funds to your account for</span>
        <span class="font-semibold">{{ $this->community->name }}</span>.
    </x-slot>

    <x-slot name="content">
        <div class="w-full flex flex-col space-y-4">
            @forelse($this->paymentMethods as $paymentMethod)
                <div class="w-full flex flex-col items-start px-4 sm:px-0">
                    <div class="text-sm text-left font-semibold text-gray-900">{{ $paymentMethod->name }}</div>
                    <div class="text-sm text-left text-gray-600">{!! data_get($paymentMethod, 'pivot.value', 'ugh') !!}</div>
                </div>
            @empty
                <div class="w-full flex flex-col items-start">
                    <div class="text-sm text-left font-semibold">No payment methods available.</div>
                </div>
            @endforelse
        </div>

        <div class="w-full mt-6 flex justify-end">
            <x-button theme="primary" class="w-full sm:w-auto" wire:click="$dispatch('closeModal')">{{ __('Close') }}
                </x-button.primary>
        </div>

    </x-slot>
</x-modal.livewire>
