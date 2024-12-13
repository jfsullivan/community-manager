<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('community-manager::Community Dashboard') }}
        </h2>
    </x-slot>

    @livewire(config('community-manager.dashboard_view'))

</x-layouts.app>
