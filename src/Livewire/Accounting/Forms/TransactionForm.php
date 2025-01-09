<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Forms;

use Brick\Money\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use JamesMills\LaravelTimezone\Facades\Timezone;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;
use Livewire\Attributes\Rule;
use Livewire\Form;

class TransactionForm extends Form
{
    public $transaction;

    public $community_id = null;

    #[Rule('required|integer', onUpdate: false)]
    public $type_id = null;

    #[Rule('required|integer', onUpdate: false)]
    public $user_id = null;

    #[Rule('nullable|required_if:type_id,5,type_id,6|integer', onUpdate: false)]
    public $transfer_user_id = null;

    #[Rule('required|date', onUpdate: false)]
    public $transacted_at = null;

    #[Rule('nullable|string|max:255', onUpdate: false)]
    public $description = null;

    #[Rule('required|numeric', onUpdate: false)]
    public $amount = null;

    public function setTransaction(Transaction $transaction)
    {
        $this->transaction = $transaction;

        $this->id = $this->transaction->id;
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

        $transactionType = TransactionType::find($this->type_id);

        $this->transacted_at = Timezone::convertFromLocal(Carbon::create($this->transacted_at)->toDateTimeString());

        $transactionClass = app(config('community-manager.transaction_model'));
        $transaction = $transactionClass::create([
            'community_id' => $this->community_id,
            'type_id' => $this->type_id,
            'user_id' => $this->user_id,
            'transfer_user_id' => $this->transfer_user_id,
            'transacted_at' => $this->transacted_at,
            'description' => $this->description,
            'amount' => Money::of($this->amount, 'USD')->multipliedBy($transactionType->direction),
        ]);

        return $transaction;
    }

    public function update()
    {
        $this->transacted_at = Timezone::convertFromLocal(Carbon::create($this->transacted_at)->toDateTimeString());

        $transactionType = TransactionType::find($this->type_id);

        $transaction = $this->transaction->update([
            'community_id' => $this->community_id,
            'type_id' => $this->type_id,
            'user_id' => $this->user_id,
            'transfer_user_id' => $this->transfer_user_id,
            'transacted_at' => $this->transacted_at,
            'description' => $this->description,
            'amount' => Money::of($this->amount, 'USD')->multipliedBy($transactionType->direction),
        ]);

        return $transaction;
    }
}
