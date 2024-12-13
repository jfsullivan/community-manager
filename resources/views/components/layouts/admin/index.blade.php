<x-layouts.admin>

    <x-slot name="leftSlideOverMenu">
        {{-- <x-community-manager::layouts.admin.slide-over-menu /> --}}
    </x-slot>

    <x-slot name="sidebar">
        <x-community-manager::layouts.admin.sidebar />
    </x-slot>

    @isset($breadcrumbs)
        <x-slot name="breadcrumbs">
            <x-breadcrumbs.item url="{{ route('community.admin.index') }}"><x-apexicon-open.speedometer class="flex-shrink-0 w-4 h-4 stroke-2 sm:h-5 sm:w-5" /></x-breadcrumbs.item>
            <x-breadcrumbs.dividers.chevron />
            {{ $breadcrumbs }}
        </x-slot>
    @endisset

    {{ $slot }}

</x-layouts.admin>
