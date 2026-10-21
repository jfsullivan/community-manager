@props(['transaction'])

@php
    $typeDetail = $transaction->model instanceof \jfsullivan\CommunityManager\Contracts\DescribesTransaction
        ? $transaction->model->transactionTypeDetail()
        : null;
@endphp

@if (filled($typeDetail))
    <div class="flex flex-col justify-center">
        <div class="whitespace-nowrap">{{ $transaction->type->name }}</div>
        <div class="text-2xs sm:text-xs text-gray-400 dark:text-zinc-500">{{ $typeDetail }}</div>
    </div>
@else
    <span class="whitespace-nowrap">{{ $transaction->type->name }}</span>
@endif
