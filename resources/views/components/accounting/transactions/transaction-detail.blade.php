@props(['transaction'])

<div class="w-full flex flex-col">
    <div class="xs:w-full sm:w-full text-xs sm:text-sm leading-5 xs:overflow-hidden sm:overflow-visible xs:truncate sm:break-normal">
        @if($transaction->type->slug == 'credit')
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