<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Pages;

use Illuminate\Support\Facades\Auth;
use jfsullivan\ApexUi\Livewire\Traits\WithFilters;
use jfsullivan\ApexUi\Livewire\Traits\WithPerPagePagination;
use jfsullivan\ApexUi\Livewire\Traits\WithSearchFilter;
use jfsullivan\ApexUi\Livewire\Traits\WithSorting;
use jfsullivan\CommunityManager\Livewire\Filters\BalanceFilter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MemberBalancesPage extends Component
{
    use BalanceFilter;
    use WithFilters;
    use WithPerPagePagination;
    use WithSearchFilter;
    use WithSorting;

    public array $selected = [];

    public $community_id;

    public function mount()
    {
        $this->perPage = 100;

        $this->defaultSortDir = [
            'name' => 'asc',
            'balance' => 'desc',
            'last_activity' => 'desc',
        ];
    }

    #[Computed]
    public function community()
    {
        $communityClass = app(config('community-manager.community_model'));

        return ($this->community_id)
            ? $communityClass::find($this->community_id)
            : Auth::user()->currentCommunity;
    }

    public function setSort($query, $key = '', $dir = null)
    {
        if ($key === 'balance') {
            $query->orderByRaw('IFNULL(balance, 0) '.($dir ?? $this->defaultSortDir['balance']));
        } elseif ($key === 'last_activity') {
            $query->orderBy('last_accessed_at', $dir ?? $this->defaultSortDir['last_activity']);
        }

        return $this->orderByMemberName($query, $dir);
    }

    /** Order by the member's name, used as the primary and tie-breaking sort. */
    protected function orderByMemberName($query, ?string $dir = null)
    {
        return $query->orderByFullName($dir ?? $this->defaultSortDir['name']);
    }

    #[Computed]
    public function recordsQuery()
    {
        $userClass = app(config('community-manager.user_model'));

        $query = $userClass::query()
            ->select(['users.id', 'users.email', 'users.profile_photo_path'])
            ->withFullName()
            ->withCommunityMembershipBalance($this->community)
            ->withCurrentMembership('community', $this->community->id)
            ->whereHas('memberships', function ($query) {
                $query->where('model_id', $this->community->id)->where('model_type', 'community');
            })
            ->when($this->balanceFilter == 'zero', fn ($query) => $query->where('balance', '=', 0))
            ->when($this->balanceFilter == 'positive', fn ($query) => $query->where('balance', '>', 0))
            ->when($this->balanceFilter == 'negative', fn ($query) => $query->where('balance', '<', 0))
            ->when($this->searchFilter, fn ($query, $searchTerm) => $query->searchByFullName($searchTerm));

        return $this->applySorting($query);
    }

    #[Computed]
    public function records()
    {
        return $this->applyPagination($this->recordsQuery);
    }

    #[On('transaction-created')]
    #[On('transaction-deleted')]
    #[On('transaction-updated')]
    public function render()
    {
        return view('community-manager::livewire.accounting.pages.member-balances-page')
            ->layout(config('community-manager.admin_layout'));
    }
}
