<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Pages;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use jfsullivan\ApexUi\Livewire\Traits\WithFilters;
use jfsullivan\ApexUi\Livewire\Traits\WithPerPagePagination;
use jfsullivan\ApexUi\Livewire\Traits\WithSearchFilter;
use jfsullivan\ApexUi\Livewire\Traits\WithSorting;
use jfsullivan\CommunityManager\Enums\TransactionMethod;
use jfsullivan\CommunityManager\Livewire\Filters\TransactionTypeFilter;
use jfsullivan\CommunityManager\Models\TransactionType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class CommunityTransactionsPage extends Component
{
    use TransactionTypeFilter;
    use WithFilters;
    use WithPerPagePagination;
    use WithSearchFilter;
    use WithSorting;

    public array $selected = [];

    public $community_id;

    public function mount()
    {
        $this->methodFilter = $this->normaliseMethodFilter($this->methodFilter);

        $this->defaultSortDir = [
            'date' => 'desc',
            'member' => 'asc',
            'transaction' => 'asc',
            'type' => 'asc',
            'amount' => 'asc',
        ];
    }

    public function setSort($query, $key = '', $dir = null)
    {
        switch ($key) {
            case 'date':
            default:
                $query->orderBy('transactions.transacted_at', $dir ?? $this->defaultSortDir['date'])
                    ->orderBy('transactions.id', 'desc'); // Add unique column as tiebreaker

                break;
            case 'member':
                $query->when(config('member-manager.name_type') == 'single', function ($query) use ($dir) {
                    $query->orderBy('users.name', $dir ?? $this->defaultSortDir['member']);
                }, function ($query) use ($dir) {
                    $query->orderBy('users.first_name', $dir ?? $this->defaultSortDir['member'])
                        ->orderBy('users.last_name', $dir ?? $this->defaultSortDir['member']);
                })
                    ->orderBy('transactions.transacted_at', $this->defaultSortDir['date'])
                    ->orderBy('transactions.id', 'desc'); // Add unique column as tiebreaker

                break;
            case 'transaction':
                $query->orderBy('transactions.description', $dir ?? $this->defaultSortDir['transaction'])
                    ->orderBy('transactions.transacted_at', $this->defaultSortDir['date'])
                    ->orderBy('transactions.id', 'desc'); // Add unique column as tiebreaker

                break;
            case 'amount':
                $query->orderBy('transactions.amount', $dir ?? $this->defaultSortDir['amount'])
                    ->orderBy('transactions.transacted_at', $this->defaultSortDir['date'])
                    ->orderBy('transactions.id', 'desc'); // Add unique column as tiebreaker

                break;
            case 'type':
                $query->orderBy('transaction_types.name', $dir ?? $this->defaultSortDir['type'])
                    ->orderBy('transactions.transacted_at', $this->defaultSortDir['date'])
                    ->orderBy('transactions.id', 'desc'); // Add unique column as tiebreaker

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
        return TransactionType::select(['id', 'slug', 'name'])->get();
    }

    /**
     * Transaction method values (TransactionMethod). Untyped so links from
     * before the filter took several methods (?methodFilter=venmo) still load.
     *
     * @var array<int, string>|string|null
     */
    #[Url(history: true)]
    public $methodFilter = [];

    /** 7d | 30d | 90d | this_year | last_year */
    #[Url(history: true)]
    public ?string $periodFilter = null;

    /** @return array<string, string> value => label */
    public function periodOptions(): array
    {
        return [
            '7d' => 'Last 7 days',
            '30d' => 'Last 30 days',
            '90d' => 'Last 90 days',
            'this_year' => 'This year',
            'last_year' => 'Last year',
        ];
    }

    public function activeFilterCount(): int
    {
        return count(array_filter([$this->transactionTypeSlugs(), $this->methodFilterValues(), $this->periodFilter]));
    }

    public function clearAllFilters(): void
    {
        $this->reset('transactionTypeFilter', 'methodFilter', 'periodFilter');
        $this->resetLoadMore();
    }

    public function updated(string $property): void
    {
        if (in_array(str($property)->before('.')->toString(), ['transactionTypeFilter', 'methodFilter', 'periodFilter'], true)) {
            $this->resetLoadMore();
        }
    }

    /**
     * The selected method values, empty when the filter is off.
     *
     * @return array<int, string>
     */
    public function methodFilterValues(): array
    {
        return $this->normaliseMethodFilter($this->methodFilter);
    }

    /**
     * Wrap a single value and drop blanks and unknown methods.
     *
     * @return array<int, string>
     */
    private function normaliseMethodFilter(mixed $value): array
    {
        return collect(Arr::wrap($value))
            ->filter(fn ($item) => is_string($item) && TransactionMethod::tryFrom($item) !== null)
            ->unique()
            ->values()
            ->all();
    }

    /** @return array{0: Carbon, 1: Carbon}|null */
    private function periodRange(): ?array
    {
        $now = now();

        return match ($this->periodFilter) {
            '7d' => [$now->copy()->subDays(7), $now],
            '30d' => [$now->copy()->subDays(30), $now],
            '90d' => [$now->copy()->subDays(90), $now],
            'this_year' => [$now->copy()->startOfYear(), $now],
            'last_year' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default => null,
        };
    }

    #[Computed]
    public function transactionQuery()
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        $query = $transactionClass::query()
            ->with(['transferPartner:id,first_name,last_name', 'type:id,name,slug', 'user:id,first_name,last_name'])
            ->select(['transactions.*'])
            ->withRelatedInfo()
            ->leftJoin('transaction_types', 'transactions.type_id', '=', 'transaction_types.id')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.community_id', $this->community->id)
            ->when($this->transactionTypeSlugs(), fn ($query, $slugs) => $query->whereIn('transaction_types.slug', $slugs))
            ->when($this->methodFilterValues(), fn ($query, $methods) => $query->whereIn('transactions.method', $methods))
            ->when($this->periodRange(), fn ($query, $range) => $query->whereBetween('transactions.transacted_at', $range))
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
