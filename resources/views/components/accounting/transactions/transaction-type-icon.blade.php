@props(['type'])

@if($type === 'entry-fee')
    <div title="Entry Fee">
        <flux:icon name="apex-ui.coins" class="w-4 h-4 text-red-600 stroke-1.5" />
    </div>
@elseif($type === 'credit')
    <div title="Credit">
        <flux:icon name="apex-ui.coins-stacked" class="w-4 h-4 text-green-600 stroke-1.5" />
    </div>
@elseif($type === 'deposit')
    <div title="deposit">
        <flux:icon name="apex-ui.coins-stacked" class="w-4 h-4 text-green-600 stroke-1.5" />
    </div>
@elseif($type === 'withdrawal')
    <div title="Withdrawal">
        <flux:icon name="apex-ui.wallet" class="w-4 h-4 text-red-600 stroke-1.5" />
    </div>
@elseif($type === 'prize-winner')
    <div title="Prize Winner">
        <flux:icon name="apex-ui.coins-stacked" class="w-4 h-4 text-green-600 stroke-1.5" />
    </div>
@elseif($type === 'transfer-in')
    <div title="Transfer In">
        <flux:icon name="apex-ui.coins-swap" class="w-4 h-4 text-green-600 stroke-1.5" />
    </div>
@elseif($type === 'transfer-out')
    <div title="Transfer Out">
        <flux:icon name="apex-ui.coins-swap" class="w-4 h-4 text-red-600 stroke-1.5" />
    </div>
@endif