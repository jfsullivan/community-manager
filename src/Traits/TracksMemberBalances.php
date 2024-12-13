<?php

namespace jfsullivan\CommunityManager\Traits;

use jfsullivan\CommunityManager\Models\PaymentMethod;

trait TracksMemberBalances
{
    public function transactions()
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        return $this->hasMany($transactionClass::class);
    }

    public function paymentMethods()
    {
        return $this->belongsToMany(PaymentMethod::class, 'community_payment_methods')->withPivot('value');
    }

    public function memberBalance($user)
    {
        return $this->transactions()->forUser($user)->sum('amount');
    }
}
