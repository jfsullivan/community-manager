<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Traits;

use Illuminate\Support\Facades\Auth;
use jfsullivan\CommunityManager\Livewire\Accounting\Forms\TransactionForm;
use jfsullivan\CommunityManager\Models\TransactionType;
use Livewire\Attributes\Computed;
use Spatie\LaravelOptions\Options;

trait HasTransactionForm
{
    public TransactionForm $form;

    public static function type(): string
    {
        return 'slide-over';
    }

    public static function modalMaxWidth(): string
    {
        return 'xl';
    }

    public static function destroyOnClose(): bool
    {
        return true;
    }

    public static function fullWidthOnMobile(): bool
    {
        return true;
    }

    #[Computed]
    public function user()
    {
        return Auth::user();
    }

    #[Computed]
    public function community()
    {
        return $this->user->currentCommunity;
    }

    #[Computed]
    public function transactionTypes()
    {
        return Options::forModels(TransactionType::orderBy('name'))->append(fn (TransactionType $type) => ['slug' => $type->slug])->toArray();
    }

    public function getCustomValidation()
    {
        return [];
    }

    public function evaluateCustomInputDisplay() {}

    #[Computed]
    public function customInputData()
    {
        return [];
    }

    public function onUpdatedStateTypeId($value) {}

    public function usersSearchQuery($searchTerm)
    {
        $userClass = config('community-manager.user_model');

        return $userClass::whereHas('communities', function ($query) {
            $query->where('communities.id', $this->community->id);
        })
            ->select('id')
            ->withFullName()
            ->searchByFullName($searchTerm)
            ->orderByFullName('asc')
            ->limit(20);
    }

    public function searchUsers($searchTerm)
    {
        return Options::forModels($this->usersSearchQuery($searchTerm), label: 'full_name')->toArray();
    }

    public function searchTransferUsers($searchTerm)
    {
        return Options::forModels($this->usersSearchQuery($searchTerm)->where('id', '!=', $this->form->user_id)->ray(), label: 'full_name')->toArray();
    }

    public function render()
    {
        return view('community-manager::livewire.accounting.transaction-form-modal');
    }
}
