<?php

namespace jfsullivan\CommunityManager\Livewire\Filters;

use jfsullivan\CommunityManager\Models\TransactionType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

trait TransactionTypeFilter
{
    #[Url]
    public $transactionTypeFilter = null;

    public function mountTransactionTypeFilter()
    {
        if (! isset($this->filters['transactionTypeFilter'])) {
            $this->filters['transactionTypeFilter'] = $this->transactionTypeFilter;
        }
    }

    #[Computed]
    public function transactionType()
    {
        return TransactionType::find($this->transactionTypeFilter);
    }

    #[Computed]
    public function transactionTypes()
    {
        return TransactionType::select('id', 'name')->get();
    }
}
