{{--
    The ledger's Type cell: the type name, plus the model's DescribesTransaction
    detail line (e.g. "Round 2"). Sized down on phones, where the cell sits
    under the amount, and aligned to its right edge there.
--}}
@props(['transaction'])

@php
    $typeDetail = $transaction->model instanceof \jfsullivan\CommunityManager\Contracts\DescribesTransaction
        ? $transaction->model->transactionTypeDetail()
        : null;
@endphp

<div class="flex flex-col items-end justify-center lg:items-start">
    <div class="text-2xs sm:text-xs md:text-sm leading-4 sm:leading-5 whitespace-nowrap">{{ $transaction->type->name }}</div>
    @if (filled($typeDetail))
        <div class="text-2xs sm:text-xs text-gray-400 dark:text-zinc-500 whitespace-nowrap">{{ $typeDetail }}</div>
    @endif
</div>
