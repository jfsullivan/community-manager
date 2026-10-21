<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;

function transactionOfType(string $slug, array $attributes = []): Transaction
{
    return Transaction::factory()->create([
        'type_id' => TransactionType::where('slug', $slug)->value('id'),
        ...$attributes,
    ])->load(['type', 'transferPartner']);
}

function renderTransactionDetail(Transaction $transaction): string
{
    return (string) test()->blade(
        '<x-community-manager::accounting.transactions.transaction-detail :transaction="$transaction" class="text-gray-500" />',
        ['transaction' => $transaction],
    );
}

it('names the other member on a transfer', function (string $slug, string $label) {
    $userClass = config('community-manager.user_model');
    $partner = $userClass::factory()->create(['name' => 'Pat Partner']);

    $html = renderTransactionDetail(transactionOfType($slug, ['transfer_user_id' => $partner->id]));

    expect($html)->toContain($label)->toContain('Pat Partner');
})->with([
    'transfer out' => ['transfer-out', 'Transfer to'],
    'transfer in' => ['transfer-in', 'Transfer from'],
]);

it('shows the description for app-defined transaction types', function () {
    $html = renderTransactionDetail(transactionOfType('entry-fee', ['description' => 'Spring Classic entry fee']));

    expect($html)->toContain('Spring Classic entry fee');
});

it('merges the classes it is given', function () {
    $html = renderTransactionDetail(transactionOfType('entry-fee'));

    expect($html)->toContain('text-gray-500');
});
