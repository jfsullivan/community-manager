@props([
    'selected' => false,
    'activeIcon',
    'inactiveIcon',
    'url',
])

{{-- Community toolbar tab. Renders identically at every breakpoint: the icon
     sits inline beside the label with the same (desktop) text size everywhere,
     with selected/unselected styling on the icon, label, and bottom border.
     Only the row distribution differs by breakpoint (handled by the parent). --}}
<div {{ $attributes->class([
        'flex px-1 py-2 items-center justify-center text-sm border-b-2',
        'text-primary-600 border-primary-600 dark:text-primary-400 dark:border-primary-400' => $selected,
        'text-gray-500 dark:text-zinc-400 hover:text-gray-700 dark:hover:text-zinc-200 border-transparent hover:border-gray-300 dark:hover:border-zinc-600' => !$selected,
    ]) }}
>
    <a href="{{ $url }}" class="cursor-pointer">
        <div class="flex flex-row items-center justify-center">
            <div>
                <flux:icon :name="'apex-ui.' . ($selected ? $activeIcon : $inactiveIcon)"
                    @class([
                        'h-5 w-5 stroke-1.5 -ml-0.5 mr-2',
                        'text-primary-600 dark:text-primary-400' => $selected,
                        'text-gray-400 dark:text-zinc-500 group-hover:text-gray-500 dark:group-hover:text-zinc-400' => !$selected,
                    ]) />
            </div>
            <div class="font-medium">{{ $slot }}</div>
        </div>
    </a>
</div>
