@props([
    'selected' => false,
    'activeIcon',
    'inactiveIcon',
    'url',
])

{{-- Community toolbar tab. Mirrors the app's pool toolbar item: icon stacked
     above the label on mobile, beside it on md+, with selected/unselected
     styling on the icon, label, and bottom border. --}}
<div {{ $attributes->class([
        'flex flex-col px-1 py-1 items-center justify-end text-xs md:text-sm border-b-2 md:py-2',
        'text-primary-600 border-primary-600 dark:text-primary-400 dark:border-primary-400' => $selected,
        'text-gray-500 dark:text-zinc-400 hover:text-gray-700 dark:hover:text-zinc-200 border-transparent hover:border-gray-300 dark:hover:border-zinc-600' => !$selected,
    ]) }}
>
    <a href="{{ $url }}" class="cursor-pointer">
        <div class="flex flex-col items-center justify-end md:flex-row md:justify-center">
            <div>
                <flux:icon :name="'apex-ui.' . ($selected ? $activeIcon : $inactiveIcon)"
                    @class([
                        'h-5 w-5 stroke-1.5 md:-ml-0.5 md:mr-2 mb-0.5 md:mb-0',
                        'text-primary-600 dark:text-primary-400' => $selected,
                        'text-gray-400 dark:text-zinc-500 group-hover:text-gray-500 dark:group-hover:text-zinc-400' => !$selected,
                    ]) />
            </div>
            <div class="md:font-medium">{{ $slot }}</div>
        </div>
    </a>
</div>
