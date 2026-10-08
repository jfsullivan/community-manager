<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Pages;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use jfsullivan\ApexUi\Livewire\Traits\WithFilters;
use jfsullivan\ApexUi\Livewire\Traits\WithPerPagePagination;
use jfsullivan\ApexUi\Livewire\Traits\WithSearchFilter;
use jfsullivan\ApexUi\Livewire\Traits\WithSorting;
use jfsullivan\CommunityManager\Contracts\FiltersTransactionsByPool;
use jfsullivan\CommunityManager\Livewire\Filters\TransactionTypeFilter;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\TransactionType;
use jfsullivan\CommunityManager\Models\User;
use jfsullivan\UserTimezone\Facades\Timezone;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * @property-read Community|null $community the configured community model (a subclass of this)
 * @property-read User|null $user the configured user model (a subclass of this)
 */
class MemberTransactionHistoryPage extends Component
{
    use TransactionTypeFilter;
    use WithFilters;
    use WithPerPagePagination;
    use WithSearchFilter;
    use WithSorting;

    public $community_id;

    public $user_id;

    /** Pool id, when the app supplies a pool filter (see FiltersTransactionsByPool). */
    #[Url(history: true)]
    public ?string $poolFilter = null;

    /**
     * First and last day to include, from the range date picker (Y-m-d, the
     * viewer's timezone; presets are worked out in the viewer's browser).
     *
     * @var array{start?: string|null, end?: string|null, preset?: string|null}|null
     */
    #[Url(history: true)]
    public ?array $dateRange = null;

    public function mount()
    {
        // A member's ledger is private: only the member themself or the
        // community's owner may see it (TransactionPolicy::viewAny). Admins use
        // the admin accounting pages instead.
        abort_unless(
            $this->user && $this->community
                && app(config('community-manager.transaction_policy'))->viewAny(Auth::user(), $this->user, $this->community),
            403
        );

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
            ? $userClass::find($this->user_id)
            : Auth::user();
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
        return TransactionType::select(['id', 'slug', 'name'])->get();
    }

    #[Computed]
    public function recordQuery()
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        $query = $transactionClass::query()
            ->with(['transferPartner', 'type'])
            ->select(['transactions.*'])
            ->withRelatedInfo()
            ->leftJoin('transaction_types', 'transactions.type_id', '=', 'transaction_types.id')
            ->where('transactions.community_id', $this->community->id)
            ->where('transactions.user_id', $this->user->id)
            ->when($this->transactionTypeSlugs(), fn ($query, $slugs) => $query->whereIn('transaction_types.slug', $slugs))
            ->when($this->dayBoundary($this->dateRange['start'] ?? null), fn ($query, $from) => $query->where('transactions.transacted_at', '>=', $from))
            ->when($this->dayBoundary($this->dateRange['end'] ?? null, endOfDay: true), fn ($query, $to) => $query->where('transactions.transacted_at', '<=', $to))
            ->when($this->poolFilter !== null && $this->poolFilterProvider(), fn ($query) => $this->poolFilterProvider()->applyPoolFilter($query, $this->poolFilter))
            ->when($this->searchFilter, fn ($query, $searchTerm) => $query->search($searchTerm));

        return $this->applySorting($query);
    }

    /** The app's pool filter, if it supplies one. */
    public function poolFilterProvider(): ?FiltersTransactionsByPool
    {
        $class = config('community-manager.transaction_pool_filter');

        return $class ? app($class) : null;
    }

    /** @return array<int|string, array{name: string, detail: string|null}> pool id => option; empty without a pool filter */
    #[Computed]
    public function poolOptions(): array
    {
        return $this->poolFilterProvider()?->poolOptions($this->community, $this->user) ?? [];
    }

    /** Filters in the Filters menu that are set (Type is the primary filter). */
    public function menuFilterCount(): int
    {
        return count(array_filter([$this->poolFilter, ($this->dateRange['start'] ?? null) ?: ($this->dateRange['end'] ?? null)]));
    }

    /** Clear every filter and the search. */
    public function clearAllFilters(): void
    {
        $this->reset('transactionTypeFilter', 'poolFilter', 'dateRange');
        $this->clearSearch();
        $this->resetLoadMore();
    }

    public function updated(string $property): void
    {
        if (in_array(str($property)->before('.')->toString(), ['transactionTypeFilter', 'poolFilter', 'dateRange'], true)) {
            $this->resetLoadMore();
        }
    }

    /** Start (or end) of a Y-m-d day in the viewer's timezone, as app time; null when unset or invalid. */
    private function dayBoundary(?string $date, bool $endOfDay = false): ?CarbonInterface
    {
        if (blank($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        $day = Carbon::parse($date, Timezone::getUserTimezone());
        $boundary = Timezone::toAppTimezone($endOfDay ? $day->endOfDay() : $day->startOfDay());

        return $boundary instanceof CarbonInterface ? $boundary : null;
    }

    #[Computed]
    public function records()
    {
        return $this->applyPagination($this->recordQuery);
    }

    #[On('transaction-created')]
    #[On('transaction-deleted')]
    #[On('transaction-updated')]
    public function render()
    {
        return view('community-manager::livewire.accounting.pages.member-transaction-history-page');
    }
}
