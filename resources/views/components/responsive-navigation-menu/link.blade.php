@props(['url' => null, 'showArrow' => false])

<x-apex::menu.item :href="$url" {{ $attributes->merge(['class' => 'flex items-center text-gray-700 hover:bg-gray-50 cursor-pointer py-1']) }} >
    @if(isset($icon))
        <x-slot name="icon"><div class="flex items-center justify-center mr-3">{{ $icon }}</div></x-slot>
    @endif

    <div class="flex grow items-center">
        {{ $slot }}
    </div>

    @if($showArrow)
        <div class="flex items-center">
            <flux:icon name="apex-ui.arrow-right" class="w-3 h-3 text-gray-400" />
        </div>
    @endif

</x-apex::menu.item>
