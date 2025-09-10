<?php

namespace jfsullivan\CommunityManager\Actions;

use Brick\Money\Money;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;

class CreateTransactionAction
{
    public function execute(array $data): Transaction
    {
        $transactionType = TransactionType::find($data['type_id']);
        
        $transactionClass = app(config('community-manager.transaction_model'));
        $transaction = $transactionClass::create([
            'community_id' => $data['community_id'],
            'type_id' => $data['type_id'],
            'user_id' => $data['user_id'],
            'transfer_user_id' => $data['transfer_user_id'] ?? null,
            'transacted_at' => $data['transacted_at'],
            'description' => $data['description'],
            'amount' => Money::of($data['amount'], 'USD')->multipliedBy($transactionType->direction),
        ]);

        // Create companion transaction for transfers
        if ($this->isTransferTransaction($transactionType)) {
            $this->createCompanionTransaction($transaction, $data);
        }

        return $transaction;
    }

    protected function isTransferTransaction(TransactionType $transactionType): bool
    {
        return in_array($transactionType->id, [5, 6]); // Transfer Out (5) or Transfer In (6)
    }

    protected function createCompanionTransaction(Transaction $mainTransaction, array $data): Transaction
    {
        $mainTransactionType = $mainTransaction->type;
        
        // Determine companion transaction type
        $companionTypeId = match ($mainTransactionType->id) {
            5 => 6, // Transfer Out -> Transfer In
            6 => 5, // Transfer In -> Transfer Out
        };

        $companionTransactionType = TransactionType::find($companionTypeId);
        
        $transactionClass = app(config('community-manager.transaction_model'));
        $companionTransaction = $transactionClass::create([
            'community_id' => $data['community_id'],
            'type_id' => $companionTypeId,
            'user_id' => $data['transfer_user_id'],
            'transfer_user_id' => $data['user_id'],
            'transacted_at' => $data['transacted_at'],
            'description' => $data['description'],
            'amount' => Money::of($data['amount'], 'USD')->multipliedBy($companionTransactionType->direction),
        ]);

        return $companionTransaction;
    }
}