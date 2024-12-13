<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use JamesMills\LaravelTimezone\Facades\Timezone;
use jfsullivan\CommunityManager\Actions\Organizations\CreateTransaction;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;
use LivewireUI\Modal\ModalComponent;
use Spatie\LaravelOptions\Options;

class TransactionFormModal_OLD extends ModalComponent
{
    

    public $additional_validation = [];
    public $user_id;
    public $transaction_id;
    public $userNameValue;
    public $transferPartnerNameValue;

    public $transferDirection = false;

    public function mount()
    {

        // $this->fill([
        //     'state' => [
        //         'id' => $this->transaction->id ?? null,
        //         'organization_id' => $this->transaction->organization_id ?? $this->organization->id,
        //         'type_id' => $this->transaction->type_id ?? null,
        //         'user_id' => $this->transaction->user_id ?? $this->user_id,
        //         'transfer_user_id' => $this->transaction->transfer_user_id ?? null,
        //         'transacted_at' => ($this->transaction) ? Timezone::convertToLocal($this->transaction->transacted_at, 'Y-m-d H:i:s') : Timezone::convertToLocal(Carbon::now('UTC'), 'Y-m-d H:i:s'),
        //         'description' => $this->transaction->description ?? null,
        //         'amount' => ($this->transaction) ? money($this->transaction->amount)->formatByDecimal() : null,
        //     ],
        // ]);

        $this->userNameValue = $this->getUserNameValue();
        $this->transferPartnerNameValue = $this->getTransferPartnerNameValue();
    }

    

    // public function getTransactionProperty()
    // {
    //     return Transaction::find($this->transaction_id);
    // }

    

    public function getTransferPartnerNameValue()
    {
        return ($this->transaction && $this->transaction->type->isTransferTransaction()) ? $this->transaction->transferPartner()->withFullName()->first()->full_name : '';
    }

    public function determineInputsFromRelatedModel()
    {
    }

    public function updatedStateTypeId($value)
    {
        $this->transferDirection = false;

        if ($this->isTransferTransaction()) {
            $this->transferDirection = ($value == 5) ? 'To' : 'From';
        }

        $this->onUpdatedStateTypeId($value);
    }

    public function isTransferTransaction()
    {
        return in_array($this->state['type_id'], [ 5, 6 ]);
    }

    public function save(CreateTransaction $creator)
    {
        $this->resetErrorBag();

        $transaction = $creator->create($this->user, $this->state, $this->getCustomValidation());

        if ($this->isTransferTransaction()) {
            $this->saveCompanionTransaction($creator);
        }

        $this->dispatch('saved');

        $this->dispatch('transaction-saved');

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Success',
            'message' => (empty($this->transaction_id)) ? 'Your new transaction has been created.' : 'Your transaction has been saved.',
        ]);

        $this->closeModal();
    }

    public function saveCompanionTransaction(CreateTransaction $creator)
    {
        $creator->create(Auth::user(), [
            'transacted_at' => $this->state['transacted_at'],
            'organization_id' => $this->state['organization_id'],
            'user_id' => $this->state['transfer_user_id'],
            'transfer_user_id' => $this->state['user_id'],
            'amount' => $this->state['amount'],
            'type_id' => ($this->state['type_id'] == 5) ? 6 : 5,
            'description' => $this->state['description'],
        ]);
    }

    public function render()
    {
        $this->evaluateCustomInputDisplay();

        return view('community-manager::livewire.accounting.transaction-form', [
            'custom_input_data' => $this->customInputData,
        ]);
    }
}
