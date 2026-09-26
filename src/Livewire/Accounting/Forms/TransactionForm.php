<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Forms;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use jfsullivan\CommunityManager\Actions\CreateTransactionAction;
use jfsullivan\CommunityManager\Actions\UpdateTransactionAction;
use jfsullivan\CommunityManager\Enums\TransactionMethod;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;
use jfsullivan\UserTimezone\Concerns\SplitsDateTimes;
use Livewire\Attributes\Validate;
use Livewire\Form;

class TransactionForm extends Form
{
    use SplitsDateTimes;

    public $transaction;

    public $community_id = null;

    #[Validate('required|integer', onUpdate: false)]
    public $type_id = null;

    #[Validate('required|integer', onUpdate: false)]
    public $user_id = null;

    #[Validate('nullable|required_if:type_id,5,type_id,6|integer', onUpdate: false)]
    public $transfer_user_id = null;

    #[Validate('required|date', onUpdate: false)]
    public $transacted_date = null;

    /** Not exposed in the UI; preserves the stored time-of-day across edits. */
    public $transacted_time = null;

    #[Validate('nullable|string|max:255', onUpdate: false)]
    public $description = null;

    #[Validate('required|numeric', onUpdate: false)]
    public $amount = null;

    /** How the money moved (deposits and withdrawals only). */
    public $method = null;

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return [
            'method' => ['nullable', Rule::enum(TransactionMethod::class)],
        ];
    }

    /**
     * The method to store: only deposits and withdrawals record one, so any
     * other type saves null even if a method was picked before the type changed.
     */
    protected function methodForType(): ?string
    {
        $slug = TransactionType::find((int) $this->type_id)?->slug;

        return in_array($slug, TransactionMethod::appliesToTypes(), true) && filled($this->method) ? $this->method : null;
    }

    public function setTransaction(Transaction $transaction)
    {
        $this->transaction = $transaction;

        // $this->id = $this->transaction->id;
        $this->community_id = $this->transaction->community_id ?? Auth::user()->current_community_id;
        $this->type_id = $this->transaction->type_id;
        $this->user_id = $this->transaction->user_id;
        $this->transfer_user_id = $this->transaction->transfer_user_id;
        $this->transacted_date = $this->toUserDate($this->transaction->transacted_at);
        $this->transacted_time = $this->toUserTime($this->transaction->transacted_at);
        $this->description = $this->transaction->description;
        $this->method = $this->transaction->method?->value;

        // str_replace($amount->getCurrency()->getSymbol(), '', $amount->formatTo('en_US'))
        $this->amount = $this->transaction->absoluteAmountValue;
    }

    public function store()
    {
        $this->community_id = Auth::user()->current_community_id;

        $createAction = new CreateTransactionAction;

        return $createAction->execute([
            'community_id' => $this->community_id,
            'type_id' => $this->type_id,
            'user_id' => $this->user_id,
            'transfer_user_id' => $this->transfer_user_id,
            'transacted_at' => $this->combinedAppDateTime($this->transacted_date, $this->transacted_time),
            'description' => $this->description,
            'method' => $this->methodForType(),
            'amount' => $this->amount,
        ]);
    }

    public function update()
    {
        $updateAction = new UpdateTransactionAction;

        return $updateAction->execute($this->transaction, [
            'community_id' => $this->community_id,
            'type_id' => $this->type_id,
            'user_id' => $this->user_id,
            'transfer_user_id' => $this->transfer_user_id,
            'transacted_at' => $this->combinedAppDateTime($this->transacted_date, $this->transacted_time),
            'description' => $this->description,
            'method' => $this->methodForType(),
            'amount' => $this->amount,
        ]);
    }
}
