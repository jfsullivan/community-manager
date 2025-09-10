<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Forms;

use Brick\Money\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use JamesMills\LaravelTimezone\Facades\Timezone;
use jfsullivan\CommunityManager\Actions\CreateTransactionAction;
use jfsullivan\CommunityManager\Actions\UpdateTransactionAction;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;
use Livewire\Attributes\Validate;
use Livewire\Form;

class TransactionForm extends Form
{
    public $transaction;

    public $community_id = null;

    #[Validate('required|integer', onUpdate: false)]
    public $type_id = null;

    #[Validate('required|integer', onUpdate: false)]
    public $user_id = null;

    #[Validate('nullable|required_if:type_id,5,type_id,6|integer', onUpdate: false)]
    public $transfer_user_id = null;

    #[Validate('required|date', onUpdate: false)]
    public $transacted_at = null;

    #[Validate('nullable|string|max:255', onUpdate: false)]
    public $description = null;

    #[Validate('required|numeric', onUpdate: false)]
    public $amount = null;

    public function setTransaction(Transaction $transaction)
    {
        $this->transaction = $transaction;

        // $this->id = $this->transaction->id;
        $this->community_id = $this->transaction->community_id ?? Auth::user()->current_community_id;
        $this->type_id = $this->transaction->type_id;
        $this->user_id = $this->transaction->user_id;
        $this->transfer_user_id = $this->transaction->transfer_user_id;
        $this->transacted_at = Timezone::convertToLocal($this->transaction->transacted_at, 'Y-m-d H:i:s');
        $this->description = $this->transaction->description;

        // str_replace($amount->getCurrency()->getSymbol(), '', $amount->formatTo('en_US'))
        $this->amount = $this->transaction->absoluteAmountValue;
    }

    public function store()
    {
        $this->community_id = Auth::user()->current_community_id;
        $this->transacted_at = Timezone::convertFromLocal(Carbon::create($this->transacted_at)->toDateTimeString());

        $createAction = new CreateTransactionAction();
        
        return $createAction->execute([
            'community_id' => $this->community_id,
            'type_id' => $this->type_id,
            'user_id' => $this->user_id,
            'transfer_user_id' => $this->transfer_user_id,
            'transacted_at' => $this->transacted_at,
            'description' => $this->description,
            'amount' => $this->amount,
        ]);
    }

    public function update()
    {
        $this->transacted_at = Timezone::convertFromLocal(Carbon::create($this->transacted_at)->toDateTimeString());

        $updateAction = new UpdateTransactionAction();
        
        return $updateAction->execute($this->transaction, [
            'community_id' => $this->community_id,
            'type_id' => $this->type_id,
            'user_id' => $this->user_id,
            'transfer_user_id' => $this->transfer_user_id,
            'transacted_at' => $this->transacted_at,
            'description' => $this->description,
            'amount' => $this->amount,
        ]);
    }
}
