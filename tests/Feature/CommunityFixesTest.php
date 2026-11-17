<?php

use jfsullivan\CommunityManager\CommunityManager;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Tests\User;

it('purges a community: clears current-community pointers and memberships, then deletes it', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $bystander = User::factory()->create();
    $community = createCommunity($owner);
    $other = createCommunity($bystander);

    addCommunityMember($community, $member);
    $member->save();
    $owner->forceFill(['current_community_id' => $community->id])->save();
    $bystander->forceFill(['current_community_id' => $other->id])->save();

    $community->purge();

    expect(Community::find($community->id))->toBeNull()
        ->and($owner->fresh()->current_community_id)->toBeNull()
        ->and($member->fresh()->current_community_id)->toBeNull()
        ->and($bystander->fresh()->current_community_id)->toBe($other->id)
        ->and(DB::table('memberships')->where('model_id', $community->id)->count())->toBe(0);
});

it('removes a user from a community', function () {
    $member = User::factory()->create();
    $community = createCommunity();
    addCommunityMember($community, $member);
    $member->save();

    $community->removeUser($member->fresh());

    expect($member->fresh()->current_community_id)->toBeNull()
        ->and($community->members()->whereKey($member->id)->exists())->toBeFalse();
});

it('lists every user including the owner, without a missing owner', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $community = createCommunity($owner);
    addCommunityMember($community, $member);

    expect($community->allUsers()->pluck('id')->sort()->values()->all())->toBe([$owner->id, $member->id]);

    $community->forceFill(['user_id' => null])->save();

    expect($community->fresh()->allUsers()->pluck('id')->all())->toBe([$member->id]);
});

it('uses USD by default and the configured currency otherwise', function () {
    $community = createCommunity();

    expect($community->currency)->toBe('USD')
        ->and(CommunityManager::formatCents(-1234))->toBe('-$12.34');

    config()->set('community-manager.currency', 'eur');

    expect($community->currency)->toBe('EUR')
        ->and(Transaction::factory()->make(['amount' => 500])->amount->getCurrency()->getCurrencyCode())->toBe('EUR')
        ->and(CommunityManager::formatCents(1234))->toContain('12.34');
});
