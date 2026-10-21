<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;
use jfsullivan\CommunityManager\Tests\DescribedItem;

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

it('lets the transaction\'s model say what the money was for', function () {
    Schema::create('described_items', function (Blueprint $table) {
        $table->id();
        $table->string('title')->nullable();
        $table->string('subtitle')->nullable();
        $table->string('type_detail')->nullable();
    });

    $item = DescribedItem::create(['title' => 'Spring Classic Skins', 'subtitle' => 'Spring Classic', 'type_detail' => 'Round 2']);
    $transaction = transactionOfType('entry-fee', ['description' => 'Entry fee', 'model_type' => $item->getMorphClass(), 'model_id' => $item->id]);

    expect(renderTransactionDetail($transaction))->toContain('Spring Classic Skins')->toContain('Spring Classic')->not->toContain('Entry fee')
        ->and((string) test()->blade('<x-community-manager::accounting.transactions.transaction-type-detail :transaction="$transaction" />', ['transaction' => $transaction]))
        ->toContain('Entry Fee')->toContain('Round 2');
});

it('merges the classes it is given', function () {
    $html = renderTransactionDetail(transactionOfType('entry-fee'));

    expect($html)->toContain('text-gray-500');
});
