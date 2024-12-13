<?php

namespace jfsullivan\CommunityManager\Traits;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use jfsullivan\MemberManager\Traits\HasMemberships;

trait HasCommunityMemberships
{
    use HasMemberships;
    use ChecksForFeatures;

    /**************************************************************************
     * Model Relationships
    ***************************************************************************/
    public function currentCommunity()
    {
        return $this->belongsTo($this->communityClass(), 'current_community_id');
    }

    public function communities()
    {
        return $this->morphedByMany($this->communityClass(), 'model', 'memberships')->withTimestamps();
    }

    /**************************************************************************
     * Model Scopes
    ***************************************************************************/
    public function scopeWithCommunityMembershipBalance($query, $community)
    {
        $query->withExpression('balances', function ($query) use ($community) {
            $query->from('transactions')
                ->select('user_id')
                ->selectRaw("SUM( transactions.amount ) as balance")
                ->where('community_id', $community->id)
                ->groupBy('user_id');
        })
        ->leftJoin('balances', 'users.id', '=', 'balances.user_id')
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

    /**************************************************************************
     * Model Methods
    ***************************************************************************/
    public function communityClass()
    {
        return config('community-manager.community_model');
    }

    public function hasCurrentCommunity()
    {
        return ! is_null($this->current_community_id);
    }

    public function isCurrentCommunity($community)
    {
        return $this->currentCommunity && $community->id === $this->currentCommunity->id;
    }

    public function switchCommunity($community)
    {
        if (! $this->belongsToCommunity($community)) {
            return false;
        }

        $this->forceFill([
            'current_community_id' => $community->id,
        ])->save();

        $this->setRelation('currentCommunity', $community);

        return true;
    }

    public function hasCommunities()
    {
        return $this->allCommunities()->count() > 0;
    }

    public function allCommunities()
    {
        if (method_exists($this, 'ownedCommunities')) {
            return $this->communities->merge($this->ownedCommunities)->sortBy('name');
        }

        return $this->communities->sortBy('name');
    }

    public function belongsToCommunity($community)
    {
        return $this->communities->contains(function ($org) use ($community) {
            return $org->id === $community->id;
        }) || $this->ownsCommunity($community);
    }
}
