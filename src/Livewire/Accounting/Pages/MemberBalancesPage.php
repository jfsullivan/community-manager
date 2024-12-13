<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Pages;

use Illuminate\Support\Facades\Auth;
use jfsullivan\CommunityManager\Livewire\Filters\BalanceFilter;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\UiKit\Livewire\Datatable\Filters\SearchFilter;
use jfsullivan\UiKit\Livewire\Datatable\WithFilters;
use jfsullivan\UiKit\Livewire\Datatable\WithPerPagePagination;
use jfsullivan\UiKit\Livewire\Datatable\WithSelectables;
use jfsullivan\UiKit\Livewire\Datatable\WithSorting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class MemberBalancesPage extends Component
{
    use WithPerPagePagination;
    use WithSelectables;
    use SearchFilter;
    use WithFilters;
    use WithSorting;
    use BalanceFilter;

    public $community_id;

    public function mount()
    {
        $this->perPage = 100;

        $this->defaultSortDir = [
            'name' => 'asc',
            'balance' => 'desc',
            'last_activity' => 'desc'
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
        switch ($key) {
            case 'name':
            default:
                $query->when((config('member-manager.name_type') == 'single'), function ($query) {
                    $query->orderBy('users.name', $dir ?? $this->defaultSortDir['name']);
                }, function ($query) {
                    $query->orderBy('users.first_name', $dir ?? $this->defaultSortDir['name'])
                        ->orderBy('users.last_name', $dir ?? $this->defaultSortDir['name']);
                });

                break;
            case 'balance':
                $query->orderByRaw('IFNULL(balance, 0) ' . $dir ?? $this->defaultSortDir['balance'])
                        ->orderBy('users.name', $dir ?? $this->defaultSortDir['name']);

                break;

            case 'last_activity':
                $query->orderBy('last_accessed_at', $dir ?? $this->defaultSortDir['last_accessed_at'])
                    ->orderBy('users.name', $dir ?? $this->defaultSortDir['name']);

                break;
        }

        return $query;
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
            ->whereHas('memberships', function($query) {
                $query->where('model_id', $this->community->id)->where('model_type', 'community');
            })
            ->when($this->balanceFilter == 'zero', fn ($query) => $query->where('balance', '=', 0))
            ->when($this->balanceFilter == 'positive', fn ($query) => $query->where('balance', '>', 0))
            ->when($this->balanceFilter == 'negative', fn ($query) => $query->where('balance', '<', 0))
            ->when($this->searchFilter, fn ($query, $searchTerm) => $query->searchByFullName($searchTerm));

        // $query = $this->community->memberships()->with(['user' => function($query) {
        //         $query->withFullName()->select(['id', 'name', 'profile_photo_path']);
        //     }])
        //     ->withMemberBalances($this->community)
        //     ->select(['balances.*', 'memberships.user_id'])
        //     // ->select(['users.id', 'users.profile_photo_path', 'balances.*', 'memberships.user_id'])
        //     // ->withFullName()
        //     ->when($this->filters['balance'] == 'non-zero', fn ($query) => $query->where('balances.balance', '!=', 0))
        //     ->when($this->filters['balance'] == 'positive', fn ($query) => $query->where('balances.balance', '>', 0))
        //     ->when($this->filters['balance'] == 'negative', fn ($query) => $query->where('balances.balance', '<', 0))
        //     ->when($this->filters['search'], fn ($query, $searchTerm) => $query->where('users.name', 'LIKE', '%'.$searchTerm.'%'));

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
        // ray($this->records->first());
        return view('community-manager::livewire.accounting.pages.member-balances-page')
            ->layout(config('community-manager.admin_layout'));
    }
}
