<?php

namespace jfsullivan\CommunityManager\Livewire\Filters;

use Livewire\Attributes\Url;

trait BalanceFilter
{
    #[Url]
    public $balanceFilter = 'all';

    /** @return array<string, string> value => label */
    public function balanceFilterOptions(): array
    {
        return [
            'all' => 'All',
            'positive' => 'Positive',
            'negative' => 'Negative',
            'zero' => 'No balance',
        ];
    }

    public function mountTransactionTypeFilter()
    {
        if (! isset($this->filters['balanceFilter'])) {
            $this->filters['balanceFilter'] = $this->balanceFilter;
        }
    }
}
