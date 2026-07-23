<x-layouts.admin>

    <x-slot name="sidebar">
        <x-community-manager::layouts.admin.sidebar />
    </x-slot>

    @isset($breadcrumbs)
        <x-slot name="breadcrumbs" :attributes="$breadcrumbs->attributes->merge(['class' => 'bg-gray-100 w-full p-2'])">
            <x-apex::breadcrumbs.item href="{{ route('community.admin.index') }}"><flux:icon name="apex-ui.speedometer" class="shrink-0 w-4 h-4 stroke-2 sm:h-5 sm:w-5" /></x-apex::breadcrumbs.item>
            {{ $breadcrumbs }}
        </x-slot>
    @endisset

    {{ $slot }}

</x-layouts.admin>
