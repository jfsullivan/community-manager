<?php

namespace jfsullivan\CommunityManager\Traits;

trait HasTransactions
{
    public function transactions()
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        return $this->morphMany($transactionClass::class, 'model');
    }
}
