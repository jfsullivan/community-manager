@props(['transaction'])

@php
    // Deposits and withdrawals lead with how the money moved ("Via Venmo");
    // the description, if any, becomes the subline.
    $via = in_array($transaction->type->slug, ['deposit', 'withdrawal']) ? $transaction->method?->viaLabel() : null;
@endphp

<div {{ $attributes->merge(['class' => 'min-w-0 flex-auto']) }}>
    @if($via)
        {{ $via }}
        @if(filled($transaction->description))
            <p class="text-2xs sm:text-xs font-normal text-gray-400 dark:text-zinc-500 truncate">{{ $transaction->description }}</p>
        @endif
    @elseif($transaction->type->isTransferTransaction())
        <x-community-manager::accounting.transactions.transaction-detail-transfers :type="$transaction->type->slug" :partner-name="$transaction->transferPartner?->name" />
    @elseif($transaction->type->slug == 'deposit')
        <p class="w-full text-xs sm:text-sm leading-6 truncate">{{ $transaction->description ?? 'New deposit' }}</p>
    @elseif($transaction->type->slug == 'withdrawal')
        <p class="w-full text-xs sm:text-sm leading-6 truncate">{{ $transaction->description ?? 'Withdrawal of funds' }}</p>
    @else
        {{-- Credits and app-defined types (entry fees, winnings, ...) show their description. --}}
        <p class="w-full text-xs sm:text-sm leading-6 truncate">{{ $transaction->description }}</p>
    @endif
</div>
