<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use jfsullivan\CommunityManager\Livewire\Memberships\Pages\MemberManagementPage;
use jfsullivan\CommunityManager\Livewire\Modals\JoinCommunity;
use jfsullivan\MemberManager\Models\Role;
use jfsullivan\MemberManager\Models\Type;
use Livewire\Livewire;

/**
 * Attach a member with an explicit start_at state so we can exercise the
 * confirmed-vs-pending distinction.
 */
function attachMember($community, $user, ?string $startAt): void
{
    $community->members()->attach($user->id, [
        'role_id' => Role::where('slug', 'member')->value('id'),
        'type_id' => Type::where('slug', 'standard')->value('id') ?? Type::where('slug', 'active')->value('id'),
        'start_at' => $startAt,
    ]);
}

beforeEach(function () {
    // The default seeded type is 'active'; JoinCommunity looks up 'standard'.
    if (! Type::where('slug', 'standard')->exists()) {
        Type::create(['name' => 'Standard', 'slug' => 'standard', 'color' => 'green']);
    }
});

it('blocks a pending member from the community and allows a confirmed one', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);
    addCommunityMember($community, $owner);
    $owner->update(['current_community_id' => $community->id]);

    $pending = $userClass::factory()->create(['current_community_id' => $community->id]);
    attachMember($community, $pending, null);

    actingAs($pending)->get(route('community.dashboard'))->assertForbidden();

    $confirmed = $userClass::factory()->create(['current_community_id' => $community->id]);
    attachMember($community, $confirmed, now());

    actingAs($confirmed)->get(route('community.dashboard'))->assertSuccessful();
});

it('excludes a pending community from the users confirmed communities and cannot switch into it', function () {
    $userClass = config('community-manager.user_model');

    $community = createCommunity();

    $pending = $userClass::factory()->create();
    attachMember($community, $pending, null);

    expect($pending->confirmedCommunities()->count())->toBe(0);
    expect($pending->belongsToCommunity($community))->toBeFalse();
    expect($pending->switchCommunity($community))->toBeFalse();
});

it('records a direct community join as pending', function () {
    $userClass = config('community-manager.user_model');

    $joiner = $userClass::factory()->create();
    $community = createCommunity();
    $community->update(['join_id' => 'ABC12345', 'password' => 'secret']);

    Livewire::actingAs($joiner)
        ->test(JoinCommunity::class)
        ->set('join_id', 'ABC12345')
        ->set('password', 'secret')
        ->call('save')
        ->assertHasNoErrors();

    $membership = $community->memberships()->where('user_id', $joiner->id)->first();

    expect($membership)->not->toBeNull();
    expect($membership->start_at)->toBeNull();
    expect($membership->latestStatus()->name)->toBe('joined');
    expect($membership->latestStatus()->reason)->toBe('pending-self-join');
});

it('tells a user their existing request is pending rather than already a member', function () {
    $userClass = config('community-manager.user_model');

    $joiner = $userClass::factory()->create();
    $community = createCommunity();
    $community->update(['join_id' => 'ABC12345', 'password' => 'secret']);
    attachMember($community, $joiner, null);

    Livewire::actingAs($joiner)
        ->test(JoinCommunity::class)
        ->set('join_id', 'ABC12345')
        ->set('password', 'secret')
        ->call('save')
        ->assertHasErrors('form');
});

it('confirms a pending membership so the member gains access', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);
    addCommunityMember($community, $owner);
    $owner->update(['current_community_id' => $community->id]);

    $pending = $userClass::factory()->create();
    attachMember($community, $pending, null);

    Livewire::actingAs($owner)
        ->test(MemberManagementPage::class, ['community_id' => $community->id])
        ->call('confirmMembership', $pending->id);

    $membership = $community->memberships()->where('user_id', $pending->id)->first();
    expect($membership->start_at)->not->toBeNull();
    expect($community->hasConfirmedMember($pending->id))->toBeTrue();
    expect($membership->latestStatus()->reason)->toBe('confirmed-by-admin');
});

it('rejects a pending membership by tombstoning without deleting the row', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);
    addCommunityMember($community, $owner);
    $owner->update(['current_community_id' => $community->id]);

    $pending = $userClass::factory()->create();
    attachMember($community, $pending, null);

    Livewire::actingAs($owner)
        ->test(MemberManagementPage::class, ['community_id' => $community->id])
        ->call('rejectMembership', $pending->id);

    $membership = $community->memberships()->where('user_id', $pending->id)->first();
    expect($membership)->not->toBeNull();
    expect($membership->end_at)->not->toBeNull();
    expect($membership->latestStatus()->name)->toBe('removed');
    expect($membership->latestStatus()->reason)->toBe('rejected-by-admin');
    expect($community->hasConfirmedMember($pending->id))->toBeFalse();
});

it('forbids a non-owner from confirming or rejecting', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);

    $member = $userClass::factory()->create(['current_community_id' => $community->id]);
    attachMember($community, $member, now());

    $pending = $userClass::factory()->create();
    attachMember($community, $pending, null);

    Livewire::actingAs($member)
        ->test(MemberManagementPage::class, ['community_id' => $community->id])
        ->call('confirmMembership', $pending->id)
        ->assertForbidden();
});

it('surfaces banned members under the banned filter and lifts bans', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);
    addCommunityMember($community, $owner);
    $owner->update(['current_community_id' => $community->id]);

    $banned = $userClass::factory()->create(['first_name' => 'Bella', 'last_name' => 'Banished']);
    attachMember($community, $banned, now()->subMonth());
    $community->banMember($banned->id, 'conduct');

    Livewire::actingAs($owner)
        ->test(MemberManagementPage::class, ['community_id' => $community->id])
        ->set('statusFilter', 'banned')
        ->assertSee('Bella')
        ->assertSee('Banned')
        ->assertSee('Lift Ban')
        ->call('liftBan', $banned->id)
        ->assertDispatched('refresh-members-list');

    expect($community->hasBannedMember($banned->id))->toBeFalse();
});

it('forbids a non-owner from lifting a ban', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);

    $member = $userClass::factory()->create(['current_community_id' => $community->id]);
    attachMember($community, $member, now());

    $banned = $userClass::factory()->create();
    attachMember($community, $banned, now()->subMonth());
    $community->banMember($banned->id);

    Livewire::actingAs($member)
        ->test(MemberManagementPage::class, ['community_id' => $community->id])
        ->call('liftBan', $banned->id)
        ->assertForbidden();

    expect($community->hasBannedMember($banned->id))->toBeTrue();
});

it('shows join status instead of a role for unconfirmed members', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);
    addCommunityMember($community, $owner);
    $owner->update(['current_community_id' => $community->id]);

    $invited = $userClass::factory()->create(['first_name' => 'Ivy', 'last_name' => 'Iota', 'email' => 'ivy@example.test']);
    attachMember($community, $invited, null);
    $community->invitations()->create(['email' => 'ivy@example.test', 'user_id' => $invited->id, 'last_sent_at' => now()]);

    $pending = $userClass::factory()->create(['first_name' => 'Penny', 'last_name' => 'Quill']);
    attachMember($community, $pending, null);

    Livewire::actingAs($owner)
        ->test(MemberManagementPage::class, ['community_id' => $community->id])
        ->set('statusFilter', 'pending')
        ->assertSeeInOrder(['Ivy Iota', 'Invited', 'Penny Quill', 'Pending']);
});

it('surfaces only pending rows under the pending filter', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create(['first_name' => 'Olive', 'last_name' => 'Owner']);
    $community = createCommunity($owner);
    addCommunityMember($community, $owner);
    $owner->update(['current_community_id' => $community->id]);

    $confirmed = $userClass::factory()->create(['first_name' => 'Connie', 'last_name' => 'Confirmed']);
    attachMember($community, $confirmed, now());

    $pending = $userClass::factory()->create(['first_name' => 'Penny', 'last_name' => 'Pending']);
    attachMember($community, $pending, null);

    Livewire::actingAs($owner)
        ->test(MemberManagementPage::class, ['community_id' => $community->id])
        ->set('statusFilter', 'pending')
        ->assertSee('Penny')
        ->assertDontSee('Connie');
});
