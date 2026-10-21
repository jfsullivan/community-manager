<?php

use Illuminate\Support\Facades\Gate;
use jfsullivan\CommunityManager\Contracts\ResolvesPremiumAccess;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;

beforeEach(function () {
    config()->set('community-manager.features.track_member_balances', true);

    $userClass = config('community-manager.user_model');
    $this->owner = $userClass::factory()->create();
    $this->member = $userClass::factory()->create();
});

function giveBalance(Community $community, $user, int $cents): void
{
    Transaction::factory()->create([
        'community_id' => $community->id,
        'user_id' => $user->id,
        'type_id' => TransactionType::where('slug', 'deposit')->value('id'),
        'amount' => $cents / 100, // the amount attribute takes dollars
    ]);
}

it('tracks member accounting only for shared communities that turned it on, behind the feature flag', function () {
    $tracking = Community::factory()->create(['track_member_balances' => true]);

    expect($tracking->tracksMemberAccounting())->toBeTrue()
        ->and(Community::factory()->create(['track_member_balances' => false])->tracksMemberAccounting())->toBeFalse()
        ->and(Community::factory()->create(['track_member_balances' => true, 'is_personal' => true])->tracksMemberAccounting())->toBeFalse();

    config()->set('community-manager.features.track_member_balances', false);

    expect($tracking->tracksMemberAccounting())->toBeFalse();
});

it('only shows balances and add funds where the community tracks them, admins included', function () {
    $tracking = createCommunity($this->owner);
    $tracking->update(['track_member_balances' => true]);
    $notTracking = createCommunity($this->owner);
    $notTracking->update(['track_member_balances' => false]);

    expect(Gate::forUser($this->owner)->allows('add-funds', $tracking))->toBeTrue()
        ->and(Gate::forUser($this->owner)->allows('view-member-balance', $tracking))->toBeTrue()
        ->and(Gate::forUser($this->owner)->allows('add-funds', $notTracking))->toBeFalse()
        ->and(Gate::forUser($this->owner)->allows('view-member-balance', $notTracking))->toBeFalse();
});

it('returns 404 from the accounting pages of a community that does not track balances', function () {
    createRoute('home', 'home');
    $community = createCommunity($this->owner);
    $community->update(['track_member_balances' => false]);
    addCommunityMember($community, $this->owner);
    $this->owner->save();

    actingAs($this->owner)->get(route('community.admin.accounting.transactions'))->assertNotFound();
    actingAs($this->owner)->get(route('community.members.transactions', ['user_id' => $this->owner->id]))->assertNotFound();
});

it('works out an entry fee shortfall from the spendable balance and the allowance', function () {
    $community = Community::factory()->create(['track_member_balances' => true, 'require_funds_for_entry_fees' => true, 'entry_fee_allowance_cents' => 500]);
    giveBalance($community, $this->member, 1000);

    expect($community->spendableBalanceCents($this->member))->toBe(1000)
        // $20 fee, $10 balance, $5 allowance: $5 short.
        ->and($community->entryFeeShortfallCents($this->member, 2000))->toBe(500)
        ->and($community->entryFeeShortfallCents($this->member, 1500))->toBe(0);
});

it('never reports a shortfall when funds are not required', function () {
    $community = Community::factory()->create(['track_member_balances' => true, 'require_funds_for_entry_fees' => false, 'entry_fee_allowance_cents' => 500]);

    expect($community->requiresFundsForEntryFees())->toBeFalse()
        ->and($community->entryFeeAllowanceCents())->toBe(0)
        ->and($community->entryFeeShortfallCents($this->member, 5000))->toBe(0);
});

it('lists the owner and current admins as administrators', function () {
    $community = createCommunity($this->owner);
    $userClass = config('community-manager.user_model');
    $admin = $userClass::factory()->create();
    addCommunityAdmin($community, $admin);
    addCommunityMember($community, $this->member);

    expect($community->administrators()->pluck('id')->sort()->values()->all())
        ->toBe(collect([$this->owner->id, $admin->id])->sort()->values()->all());
});

it('unlocks every feature by default and asks the configured resolver otherwise', function () {
    $community = Community::factory()->create();

    expect($community->allowsFeature('messages'))->toBeTrue()
        ->and($community->hasPremiumAccess())->toBeTrue()
        ->and($community->featureLimit('members'))->toBeNull();

    app()->bind('test.premium', fn () => new class implements ResolvesPremiumAccess
    {
        public function allows(Community $community, string $feature): bool
        {
            return $feature !== 'messages';
        }

        public function limit(Community $community, string $key): ?int
        {
            return $key === 'members' ? 8 : null;
        }
    });
    config()->set('community-manager.premium.resolver', 'test.premium');

    expect($community->allowsFeature('messages'))->toBeFalse()
        ->and($community->hasPremiumAccess())->toBeTrue()
        ->and($community->featureLimit('members'))->toBe(8);
});

it('lists only shared communities that are not hidden', function () {
    $shared = createCommunity($this->owner);
    Community::factory()->create(['user_id' => $this->owner->id, 'is_personal' => true]);

    expect($this->owner->fresh()->sharedCommunities()->pluck('id')->all())->toBe([$shared->id])
        ->and($this->owner->fresh()->hasSharedCommunities())->toBeTrue();
});
