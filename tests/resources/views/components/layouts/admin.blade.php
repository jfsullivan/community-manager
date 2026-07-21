<x-layouts.base>

    <x-slot name="header">
        {{ $header ?? '' }}
    </x-slot>

    {{ $slot }}

</x-layouts.base>
