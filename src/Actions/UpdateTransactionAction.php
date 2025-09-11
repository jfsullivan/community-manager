<?php

namespace jfsullivan\CommunityManager\Actions;

use Brick\Money\Money;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;

class UpdateTransactionAction
{
    public function execute(Transaction $transaction, array $data): Transaction
    {
        $transactionType = TransactionType::find($data['type_id']);

        // Check if this was previously a transfer transaction
        $wasTransferTransaction = $this->isTransferTransaction($transaction->type);
        $isTransferTransaction = $this->isTransferTransaction($transactionType);

        // Find and handle existing companion transaction
        $companionTransaction = null;
        if ($wasTransferTransaction) {
            $companionTransaction = $this->findCompanionTransaction($transaction);
        }

        // Update the main transaction
        $transaction->update([
            'community_id' => $data['community_id'],
            'type_id' => $data['type_id'],
            'user_id' => $data['user_id'],
            'transfer_user_id' => $data['transfer_user_id'] ?? null,
            'transacted_at' => $data['transacted_at'],
            'description' => $data['description'],
            'amount' => Money::of($data['amount'], 'USD')->multipliedBy($transactionType->direction),
        ]);

        // Handle companion transaction logic
        if ($wasTransferTransaction && ! $isTransferTransaction) {
            // Was transfer, now isn't - delete companion
            if ($companionTransaction) {
                $companionTransaction->delete();
            }
        } elseif (! $wasTransferTransaction && $isTransferTransaction) {
            // Wasn't transfer, now is - create companion
            $this->createCompanionTransaction($transaction, $data);
        } elseif ($wasTransferTransaction && $isTransferTransaction) {
            // Was transfer, still is - update companion
            if ($companionTransaction) {
                $this->updateCompanionTransaction($companionTransaction, $transaction, $data);
            } else {
                // Companion doesn't exist, create it
                $this->createCompanionTransaction($transaction, $data);
            }
        }

        return $transaction->fresh();
    }

    protected function isTransferTransaction(TransactionType $transactionType): bool
    {
        return in_array($transactionType->id, [5, 6]); // Transfer Out (5) or Transfer In (6)
    }

    protected function findCompanionTransaction(Transaction $transaction): ?Transaction
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        return $transactionClass::where('user_id', $transaction->transfer_user_id)
            ->where('transfer_user_id', $transaction->user_id)
            ->where('community_id', $transaction->community_id)
            ->where('transacted_at', $transaction->transacted_at)
            ->where('description', $transaction->description)
            ->where('id', '!=', $transaction->id)
            ->first();
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

    protected function updateCompanionTransaction(Transaction $companionTransaction, Transaction $mainTransaction, array $data): Transaction
    {
        $companionTransactionType = $companionTransaction->type;

        $companionTransaction->update([
            'community_id' => $data['community_id'],
            'user_id' => $data['transfer_user_id'],
            'transfer_user_id' => $data['user_id'],
            'transacted_at' => $data['transacted_at'],
            'description' => $data['description'],
            'amount' => Money::of($data['amount'], 'USD')->multipliedBy($companionTransactionType->direction),
        ]);

        return $companionTransaction;
    }
}
