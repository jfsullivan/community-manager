<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Pages;

use Illuminate\Support\Facades\Auth;
use jfsullivan\CommunityManager\Livewire\Filters\TransactionTypeFilter;
use jfsullivan\CommunityManager\Models\TransactionType;
use jfsullivan\UiKit\Livewire\Datatable\Filters\SearchFilter;
use jfsullivan\UiKit\Livewire\Datatable\WithFilters;
use jfsullivan\UiKit\Livewire\Datatable\WithPerPagePagination;
use jfsullivan\UiKit\Livewire\Datatable\WithSelectables;
use jfsullivan\UiKit\Livewire\Datatable\WithSorting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MemberTransactionsPage extends Component
{
    use SearchFilter;
    use TransactionTypeFilter;
    use WithFilters;
    use WithPerPagePagination;
    use WithSelectables;
    use WithSorting;

    public $community_id;

    public $user_id;

    public function mount()
    {
        $this->perPage = 100;

        $this->defaultSortDir = [
            'date' => 'desc',
            'type' => 'asc',
            'amount' => 'asc',
        ];
    }

    public function setSort($query, $key = '', $dir = null)
    {
        switch ($key) {
            case 'date':
            default:
                $query->orderBy('transacted_at', $dir ?? $this->defaultSortDir['date']);

                break;
            case 'amount':
                $query->orderBy('amount', $dir ?? $this->defaultSortDir['amount']);

                break;
            case 'type':
                $query->orderBy('transaction_types.name', $dir ?? $this->defaultSortDir['type']);

                break;
        }

        return $query;
    }

    #[Computed]
    public function community()
    {
        $communityClass = app(config('community-manager.community_model'));

        return ($this->community_id)
            ? $communityClass::find($this->community_id)
            : Auth::user()->currentCommunity;
    }

    #[Computed]
    public function user()
    {
        $userClass = app(config('community-manager.user_model'));

        return ($this->user_id)
            ? $userClass::select('id', 'email', 'first_name', 'last_name')->withFullName()->findOrFail($this->user_id)
            : $userClass::select('id', 'email', 'first_name', 'last_name')->withFullName()->findOrFail(Auth::user()->id);
    }

    #[Computed]
    public function memberBalance()
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        return $transactionClass::query()
            ->selectRaw('SUM( amount ) as total')
            ->where('community_id', $this->community->id)
            ->where('user_id', $this->user->id)
            ->groupBy('user_id')
            ->get();
    }

    #[Computed]
    public function transactionTypes()
    {
        return TransactionType::select(['id', 'name'])->get();
    }

    #[Computed]
    public function recordQuery()
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        $query = $transactionClass::query()
            ->with(['user', 'transferPartner', 'type'])
            ->select(['transactions.*'])
            ->withRelatedInfo()
            ->leftJoin('transaction_types', 'transactions.type_id', '=', 'transaction_types.id')
            ->where('transactions.community_id', $this->community->id)
            ->where('transactions.user_id', $this->user->id)
            ->when($this->transactionTypeFilter, fn ($query, $id) => $query->where('type_id', $id))
            ->when($this->searchFilter, fn ($query, $searchTerm) => $query->search($searchTerm));

        return $this->applySorting($query);
    }

    #[Computed]
    public function records()
    {
        return $this->applyPagination($this->recordQuery);
    }

    #[On('transaction-created')]
    #[On('transaction-deleted')]
    #[On('transaction-updated')]
    public function clearSelectedTransactions()
    {
        $this->clearSelected();
    }

    public function render()
    {
        return view('community-manager::livewire.accounting.pages.member-transactions-page')
            ->layout(config('community-manager.admin_layout'));
    }
}
