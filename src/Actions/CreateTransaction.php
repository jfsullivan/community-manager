<?php

namespace jfsullivan\CommunityManager\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use JamesMills\LaravelTimezone\Facades\Timezone;
use jfsullivan\CommunityManager\Models\TransactionType;

class CreateTransaction
{
    /**
     * Validate and create a new transaction for the given community.
     *
     * @param  mixed  $user
     * @return mixed
     */
    public function create($user, array $input, array $additional_validation = [])
    {
        if ($user == 'system') {
            $userModel = app(config('community-manager.user_model'));
            $user = $userModel::where('first_name', 'System')->where('last_name', 'Account')->orderBy('id')->first();
        } else {
            Gate::forUser($user)->authorize('create-community-transaction', [$user->currentCommunity]);
        }

        $this->validate($input, $additional_validation);

        $transactionType = TransactionType::find($input['type_id']);

        $input['transacted_at'] = Timezone::convertFromLocal(Carbon::create($input['transacted_at'])->toDateTimeString());

        $input['amount'] = $input['amount'] * $transactionType->direction;

        $transactionClass = app(config('community-manager.transaction_model'));
        $transaction = $transactionClass::updateOrCreate(
            ['id' => $input['id'] ?? null],
            $input
        );

        return $transaction;
    }

    public function validate($input, $additional_validation)
    {
        Validator::make($input, array_merge($additional_validation['rules'] ?? [], [
            'transacted_at' => ['required'],
            'community_id' => ['required'],
            'user_id' => ['required'],
            'type_id' => ['required'],
            'model_type' => ['required_if:type_id,3,type_id,4'],
            'model_id' => ['required_if:type_id,3,type_id,4'],
            'transfer_user_id' => ['required_if:type_id,5,type_id,6'],
            'description' => ['max:500'],
            'amount' => ['required', 'numeric'],
        ]), array_merge($additional_validation['messages'] ?? [], [
            'transacted_at.required' => 'The Date is required.',
            'user_id.required' => 'The User is required.',
            'type_id.required' => 'The Type is required.',
            'transfer_user_id.required_if' => 'The Transfer User is required.',
            'description.max' => 'The Description must be less than 500 characters.',
            'amount.required' => 'The Amount is required.',
            'amount.numeric' => 'The Amount must be a number.',
        ]))->validate();
    }
}
