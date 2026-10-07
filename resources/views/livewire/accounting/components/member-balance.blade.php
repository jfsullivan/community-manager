{{-- A member's balance in a community, colored by sign. With $selectable it
     links to that member's transaction history; without, it's plain text
     (e.g. on the history page itself). --}}
@php
    $balanceClasses = [
        'font-semibold flex items-center',
        'hover:underline' => $selectable,
        'text-green-500 dark:text-green-400' => $formatted && $this->memberBalance->isGreaterThan(0),
        'text-red-500 dark:text-red-400' => $formatted && $this->memberBalance->isLessThan(0),
        'text-gray-900 dark:text-zinc-100' => $formatted && $this->memberBalance->isEqualTo(0),
        $class,
    ];
@endphp

<div class="flex items-center">
    @if($selectable)
        <a href="{{ route('community.members.transactions', [$this->user->id]) }}" @class($balanceClasses)>
            @if($formatted)
                <x-money :amount="$this->memberBalance" formatted :class="$class" />
            @else
                <x-money :amount="$this->memberBalance" :class="$class" />
            @endif

            <flux:icon name="apex-ui.arrow-right" class="w-3 h-3 ml-0.5 stroke-2" />
        </a>
    @else
        <span @class($balanceClasses)>
            @if($formatted)
                <x-money :amount="$this->memberBalance" formatted :class="$class" />
            @else
                <x-money :amount="$this->memberBalance" :class="$class" />
            @endif
        </span>
    @endif
</div>
