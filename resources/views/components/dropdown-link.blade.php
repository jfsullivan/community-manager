<x-dropdown.link {{ $attributes->merge(['class' => 'flex items-center text-gray-700 hover:bg-gray-50 cursor-pointer py-1']) }} >
    @if(isset($icon))
        <x-slot name="icon">{{ $icon }}</x-slot>
    @endif

    <div class="flex flex-grow items-center">
        {{ $slot }}
    </div>

    @if(isset($showArrow) && $showArrow)
        <div class="flex items-center">
            <x-apexicon-open.arrow-right class="w-3 h-3 text-gray-400" />
        </div>
    @endif

</x-dropdown.link>