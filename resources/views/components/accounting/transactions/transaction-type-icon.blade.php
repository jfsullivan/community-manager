@props(['type'])

@if($type === 'entry-fee')
    <div title="Entry Fee">
        <x-apexicon-open.coins class="w-4 h-4 text-red-600 stroke-1.5" />
    </div>
@elseif($type === 'credit')
    <div title="Credit">
        <x-apexicon-open.coins-stacked class="w-4 h-4 text-green-600 stroke-1.5" />
    </div>
@elseif($type === 'deposit')
    <div title="deposit">
        <x-apexicon-open.coins-stacked class="w-4 h-4 text-green-600 stroke-1.5" />
    </div>
@elseif($type === 'withdrawal')
    <div title="Withdrawal">
        <x-apexicon-open.wallet class="w-4 h-4 text-red-600 stroke-1.5" />
    </div>
@elseif($type === 'prize-winner')
    <div title="Prize Winner">
        <x-apexicon-open.coins-stacked class="w-4 h-4 text-green-600 stroke-1.5" />
    </div>
@elseif($type === 'transfer-in')
    <div title="Transfer In">
        <x-apexicon-open.coins-swap class="w-4 h-4 text-green-600 stroke-1.5" />
    </div>
@elseif($type === 'transfer-out')
    <div title="Transfer Out">
        <x-apexicon-open.coins-swap class="w-4 h-4 text-red-600 stroke-1.5" />
    </div>
@endif