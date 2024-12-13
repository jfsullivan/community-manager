<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Traits;

use Illuminate\Support\Facades\Auth;
use jfsullivan\CommunityManager\Livewire\Accounting\Forms\TransactionForm;
use JamesMills\LaravelTimezone\Facades\Timezone;
use jfsullivan\CommunityManager\Models\TransactionType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
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
        return Options::forModels(TransactionType::orderBy('name'))->append(fn(TransactionType $type) => ['slug' => $type->slug])->toArray();
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
        return $this->community->members()->search($searchTerm)->select('id')->withFullName()->orderByFullName('asc')->limit(20);
    }

    public function searchUsers($searchTerm)
    {
        return Options::forModels($this->usersSearchQuery($searchTerm), label: 'full_name')->toArray();
    }

    public function searchTransferUsers($searchTerm)
    {
        return Options::forModels($this->usersSearchQuery($searchTerm), label: 'full_name')->filter(fn($user) => $user->id !== $this->form->user_id)->toArray();
    }

    public function render()
    {
        return view('community-manager::livewire.accounting.transaction-form-modal');
    }
}
