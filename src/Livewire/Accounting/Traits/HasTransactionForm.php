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

    public string $userSearchTerm = '';

    public string $transferUserSearchTerm = '';

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

    #[Computed]
    public function userOptions()
    {
        $options = Options::forModels($this->usersSearchQuery($this->userSearchTerm), label: 'full_name')->toArray();

        return $this->withSelectedUserOption($options, $this->form->user_id);
    }

    #[Computed]
    public function transferUserOptions()
    {
        $options = Options::forModels(
            $this->usersSearchQuery($this->transferUserSearchTerm)->where('id', '!=', $this->form->user_id),
            label: 'full_name'
        )->toArray();

        return $this->withSelectedUserOption($options, $this->form->transfer_user_id);
    }

    /**
     * The search query only returns the first 20 matches, so when editing, the
     * bound user may fall outside them and the select would render empty.
     * Prepend the selected user whenever the options don't already include it.
     *
     * @param  array<int, array{label: string, value: mixed}>  $options
     * @return array<int, array{label: string, value: mixed}>
     */
    protected function withSelectedUserOption(array $options, $userId): array
    {
        if (! $userId || collect($options)->contains(fn ($option) => (string) $option['value'] === (string) $userId)) {
            return $options;
        }

        $userClass = config('community-manager.user_model');

        $selected = $userClass::select('id')->withFullName()->whereKey($userId)->first();

        if (! $selected) {
            return $options;
        }

        return array_merge([['label' => $selected->full_name, 'value' => $selected->id]], $options);
    }

    public function render()
    {
        return view('community-manager::livewire.accounting.transaction-form-modal');
    }
}
