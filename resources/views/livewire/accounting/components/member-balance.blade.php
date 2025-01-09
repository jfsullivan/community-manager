@php
// $headerSize = match($size) {
//     'sm' => 'text-xs',
//     'lg' => 'text-sm',
//     default => 'text-xs'
// };

// $sizeClasses = match($size) {
//     'sm' => 'text-xs',
//     'lg' => 'text-base',
//     default => 'text-sm'
// };
@endphp

<div class="flex items-center">
    <a href="{{ route('community.members.transactions', [Auth::user()->id]) }}"
        @class([
            'font-semibold hover:underline flex items-center',
            'text-green-500' => $formatted && $this->memberBalance->isGreaterThan(0),
            'text-red-500' => $formatted && $this->memberBalance->isLessThan(0),
            'text-gray-900' =>  $formatted && $this->memberBalance->isEqualTo(0),
            $class
        ])
    >
        @if($formatted)
            <x-money :amount="$this->memberBalance" formatted :class="$class" />
        @else
            <x-money :amount="$this->memberBalance" :class="$class" />
        @endif

        @if($selectable)
            <x-apexicon-open.arrow-right class="w-3 h-3 ml-0.5 stroke-2" />
        @endif
    </a>
</div>
