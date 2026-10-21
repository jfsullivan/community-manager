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
    @elseif($transaction->model instanceof \jfsullivan\CommunityManager\Contracts\DescribesTransaction)
        {{-- App-defined types (entry fees, winnings, ...) whose model says what the money was for. --}}
        <p class="w-full text-xs sm:text-sm leading-4 sm:leading-5 truncate">{{ $transaction->model->transactionTitle() ?? $transaction->description }}</p>
        @if(filled($subtitle = $transaction->model->transactionSubtitle()))
            <p class="w-full text-2xs sm:text-xs text-gray-400 dark:text-zinc-500 truncate font-normal">{{ $subtitle }}</p>
        @endif
    @else
        {{-- Credits and other types show their description. --}}
        <p class="w-full text-xs sm:text-sm leading-6 truncate">{{ $transaction->description }}</p>
    @endif
</div>
