@props(['type', 'partnerName'])

<p class="w-full text-sm font-medium leading-6 text-gray-900 truncate">
    @if($type == 'transfer-out')
        <span>Transfer to </span>
    @elseif($type == 'transfer-in')
        <span>Transfer from </span>
    @endif
    <span class="ml-1.5">{{ $partnerName }}</span>
</p>
