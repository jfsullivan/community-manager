<?php

namespace jfsullivan\CommunityManager\Models;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use jfsullivan\MemberManager\Models\Membership as MemberManagerMembership;

class Membership extends MemberManagerMembership
{
    /**************************************************************************
     * Model Scopes
    ***************************************************************************/
    public function scopeWithMemberBalances($query, $community)
    {
        $query->withExpression('balances', function ($query) use ($community) {
            $query->from('transactions')
                ->select('user_id')
                ->selectRaw("SUM( transactions.amount ) as balance")
                ->where('community_id', $community->id)
                ->groupBy('user_id');
        })
        ->leftJoin('balances', 'memberships.user_id', '=', 'balances.user_id')
        ->addSelect('balances.*');
    }

    /**************************************************************************
     * Mutators & Accessors
    ***************************************************************************/
    protected function balance(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Money::ofMinor($value ?? 0, 'USD'),
            set: function ($value) {
                return ($value instanceOf Money) 
                        ? $value->getMinorAmount()->toInt()
                        : Money::of($value, 'USD')->getMinorAmount()->toInt();
            },
        );
    }
}
