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

        // Former (ended stint) and banned members must not surface as
        // transaction subjects. Current and still-pending members remain
        // selectable. (Editing keeps its bound user via withSelectedUserOption(),
        // so an existing transaction against a now-former member still shows.)
        return $userClass::whereHas('memberships', function ($query) {
            $query->where('memberships.model_id', $this->community->id)
                ->where('memberships.model_type', $this->community->getMorphClass())
                // Not former: no end date, or the end date is still in the future.
                ->where(function ($query) {
                    $query->whereNull('memberships.end_at')
                        ->orWhere('memberships.end_at', '>=', now());
                })
                // Not banned: the latest lifecycle status isn't `banned`.
                ->whereDoesntHave('statuses', function ($statusQuery) {
                    $statusQuery->where('name', 'banned')
                        ->whereRaw('statuses.id = (select max(s2.id) from statuses s2 where s2.model_id = statuses.model_id and s2.model_type = statuses.model_type)');
                });
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
