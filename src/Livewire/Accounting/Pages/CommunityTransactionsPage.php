<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Pages;

use Illuminate\Support\Facades\Auth;
use jfsullivan\CommunityManager\Livewire\Filters\TransactionTypeFilter;
use jfsullivan\CommunityManager\Models\TransactionType;
use jfsullivan\UiKit\Livewire\Datatable\Filters\SearchFilter;
use jfsullivan\UiKit\Livewire\Datatable\WithCursorPagination;
use jfsullivan\UiKit\Livewire\Datatable\WithFilters;
use jfsullivan\UiKit\Livewire\Datatable\WithSelectables;
use jfsullivan\UiKit\Livewire\Datatable\WithSorting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CommunityTransactionsPage extends Component
{
    use SearchFilter;
    use TransactionTypeFilter;
    use WithCursorPagination;
    use WithFilters;
    use WithSelectables;
    use WithSorting;

    public $community_id;

    public $cursor;

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
        // match ($key) {
        //     'date' => $query->orderBy('transacted_at', $dir ?? $this->defaultSortDir['date']),
        //     'amount' => $query->orderBy('amount', $dir ?? $this->defaultSortDir['amount']),
        //     'type' => $query->orderBy('transaction_types.name', $dir ?? $this->defaultSortDir['type']),
        //     default => $query->orderBy('transacted_at', $dir ?? $this->defaultSortDir['date']),
        // };
        switch ($key) {
            case 'date':
            default:
                $query->orderBy('transacted_at', $dir ?? $this->defaultSortDir['date'])
                        ->orderBy('id', 'desc'); // Add unique column as tiebreaker

                break;
            case 'amount':
                $query->orderBy('amount', $dir ?? $this->defaultSortDir['amount'])
                        ->orderBy('transacted_at', $dir ?? $this->defaultSortDir['date'])
                        ->orderBy('id', 'desc'); // Add unique column as tiebreaker

                break;
            case 'type':
                $query->orderBy('transaction_types.name', $dir ?? $this->defaultSortDir['type'])
                        ->orderBy('transacted_at', $dir ?? $this->defaultSortDir['date'])
                        ->orderBy('id', 'desc'); // Add unique column as tiebreaker

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
    public function memberBalance()
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        return $transactionClass::query()
            ->selectRaw('SUM( amount ) as total')
            ->where('community_id', $this->community->id)
            ->get();
    }

    #[Computed]
    public function transactionTypes()
    {
        return TransactionType::select(['id', 'name'])->get();
    }

    #[Computed]
    public function transactionQuery()
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        $query = $transactionClass::query()
            ->with(['transferPartner:id,first_name,last_name', 'type:id,name,slug', 'user:id,first_name,last_name'])
            ->select(['transactions.*'])
            ->withRelatedInfo()
            ->where('transactions.community_id', $this->community->id)
            ->when($this->transactionTypeFilter, fn ($query, $id) => $query->where('type_id', $id))
            ->when($this->searchFilter, fn ($query, $searchTerm) => $query->search($searchTerm));

        return $this->applySorting($query);
    }

    #[Computed]
    public function records()
    {
        return $this->applyPagination($this->transactionQuery);
    }

    #[On('transaction-created')]
    #[On('transaction-deleted')]
    #[On('transaction-updated')]
    public function render()
    {
        return view('community-manager::livewire.accounting.pages.community-transactions-page')
            ->layout(config('community-manager.admin_layout'));
    }
}
