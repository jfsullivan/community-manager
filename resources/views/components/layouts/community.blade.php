<x-layouts.app title="{{ $title ?? config('app.name', 'Laravel') }}">
    <div class="flex flex-col items-center w-full pb-8">
        <x-slot name="header">
            <x-community-manager::page-header :community="$community" />
        </x-slot>

            {{ $slot }}

    </div>
</x-layouts.app>