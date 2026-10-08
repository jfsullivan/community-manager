<?php

namespace jfsullivan\CommunityManager\Livewire\Filters;

use Illuminate\Support\Arr;
use jfsullivan\CommunityManager\Models\TransactionType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * Filters by one or more transaction type slugs
 * (?transactionTypeFilter[0]=deposit&transactionTypeFilter[1]=withdrawal).
 *
 * Untyped so links from before the filter took several types still load: a
 * single slug (?transactionTypeFilter=deposit) or an old numeric id becomes a
 * one-slug list on mount.
 */
trait TransactionTypeFilter
{
    /** @var array<int, string>|string|int|null */
    #[Url(history: true)]
    public $transactionTypeFilter = [];

    public function mountTransactionTypeFilter()
    {
        $this->transactionTypeFilter = $this->normaliseTransactionTypeFilter($this->transactionTypeFilter);

        if (! isset($this->filters['transactionTypeFilter'])) {
            $this->filters['transactionTypeFilter'] = $this->transactionTypeFilter;
        }
    }

    /**
     * The selected type slugs, empty when the filter is off.
     *
     * @return array<int, string>
     */
    public function transactionTypeSlugs(): array
    {
        return array_values(array_filter(array_map('strval', Arr::wrap($this->transactionTypeFilter)), 'filled'));
    }

    /** The transaction type, when exactly one is chosen (kept for callers from the single-choice era). */
    #[Computed]
    public function transactionType(): ?TransactionType
    {
        $slugs = $this->transactionTypeSlugs();

        return count($slugs) === 1 ? TransactionType::firstWhere('slug', $slugs[0]) : null;
    }

    #[Computed]
    public function transactionTypes()
    {
        return TransactionType::select('id', 'slug', 'name')->get();
    }

    /**
     * Wrap a single value, map old numeric ids to slugs, and drop blanks and
     * unknown types.
     *
     * @return array<int, string>
     */
    protected function normaliseTransactionTypeFilter(mixed $value): array
    {
        $values = collect(Arr::wrap($value))
            ->filter(fn ($item) => is_scalar($item) && filled($item))
            ->map(fn ($item) => (string) $item)
            ->values();

        if ($values->isEmpty()) {
            return [];
        }

        $ids = $values->filter(fn (string $item) => ctype_digit($item))->map(fn (string $item) => (int) $item)->all();

        $types = TransactionType::query()
            ->whereIn('slug', $values->all())
            ->when($ids !== [], fn ($query) => $query->orWhereIn('id', $ids))
            ->get(['id', 'slug']);

        $slugs = $types->pluck('slug')->all();
        $slugsById = $types->pluck('slug', 'id');

        return $values
            ->map(fn (string $item) => in_array($item, $slugs, true) ? $item : (ctype_digit($item) ? $slugsById->get((int) $item) : null))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
