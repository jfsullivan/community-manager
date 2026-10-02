<?php

namespace jfsullivan\CommunityManager\Livewire\Filters;

use jfsullivan\CommunityManager\Models\TransactionType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * Filters by transaction type slug (?transactionTypeFilter=deposit).
 */
trait TransactionTypeFilter
{
    #[Url]
    public $transactionTypeFilter = null;

    public function mountTransactionTypeFilter()
    {
        // Links from before the filter used slugs carry the type's id.
        if (is_numeric($this->transactionTypeFilter)) {
            $this->transactionTypeFilter = TransactionType::whereKey($this->transactionTypeFilter)->value('slug');
        }

        if (! isset($this->filters['transactionTypeFilter'])) {
            $this->filters['transactionTypeFilter'] = $this->transactionTypeFilter;
        }
    }

    #[Computed]
    public function transactionType()
    {
        return filled($this->transactionTypeFilter) ? TransactionType::firstWhere('slug', $this->transactionTypeFilter) : null;
    }

    #[Computed]
    public function transactionTypes()
    {
        return TransactionType::select('id', 'slug', 'name')->get();
    }
}
