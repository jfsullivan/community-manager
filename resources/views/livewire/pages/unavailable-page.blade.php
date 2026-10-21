{{-- A member's view of something disabled (a community, or an app's kind such as a pool): no reason, no name. --}}
<div class="flex flex-col items-center w-full">
    <div class="w-full max-w-3xl px-4 sm:px-6 sm:pt-6">
        <x-apex::empty-state icon="apex-ui.lock" class="py-16">
            <x-slot:heading>This {{ $kind }} isn't available</x-slot:heading>

            <x-slot:subheading>You can't open it right now. Your other {{ \Illuminate\Support\Str::plural($kind) }} are on your home page.</x-slot:subheading>

            <x-slot:actions>
                <x-apex::button variant="primary" :href="route('home')" wire:navigate>Go to my home page</x-apex::button>
            </x-slot:actions>
        </x-apex::empty-state>
    </div>
</div>
