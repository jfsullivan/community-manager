@props(['transaction'])

@php
    // Deposits and withdrawals lead with how the money moved ("Via Venmo");
    // the description, if any, becomes the subline.
    $via = in_array($transaction->type->slug, ['deposit', 'withdrawal']) ? $transaction->method?->viaLabel() : null;
@endphp

<div class="w-full flex flex-col">
    <div class="xs:w-full sm:w-full text-xs sm:text-sm leading-5 xs:overflow-hidden sm:overflow-visible xs:truncate sm:break-normal">
        @if($via)
            {{ $via }}
            @if(filled($transaction->description))
                <p class="text-2xs sm:text-xs font-normal text-gray-400 dark:text-zinc-500 truncate">{{ $transaction->description }}</p>
            @endif
        @elseif($transaction->type->slug == 'credit')
            {{ $transaction->description }}
        @elseif($transaction->type->slug == 'deposit')
            {{ $transaction->description }}
        @elseif($transaction->type->slug == 'withdrawal')
            {{ $transaction->description ?? 'Withdrawal of funds' }}
        @elseif($transaction->type->slug == 'transfer')
            <x-community-manager::accounting.transactions.transaction-detail-transfers :transaction="$transaction"></x-community-manager::accounting.transactions.transaction-detail-transfers>
        @endif
    </div>
</div>
