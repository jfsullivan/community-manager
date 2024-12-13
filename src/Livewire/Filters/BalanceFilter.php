<?php

namespace jfsullivan\CommunityManager\Livewire\Filters;

use Livewire\Attributes\Url;

trait BalanceFilter
{
    #[Url]
    public $balanceFilter = 'all';

    public function mountTransactionTypeFilter()
    {
        if (! isset($this->filters['balanceFilter'])) {
            $this->filters['balanceFilter'] = $this->balanceFilter;
        }
    }
}
