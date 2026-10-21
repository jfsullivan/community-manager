<?php

use Illuminate\Support\Facades\Notification;
use jfsullivan\CommunityManager\Livewire\Memberships\Pages\MemberManagementPage;
use jfsullivan\CommunityManager\Livewire\Modals\JoinCommunity;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Notifications\MembershipRejected;
use jfsullivan\MemberManager\Models\Role;
use jfsullivan\MemberManager\Models\Type;
use Livewire\Livewire;

beforeEach(function () {
    if (! Type::where('slug', 'standard')->exists()) {
        Type::create(['name' => 'Standard', 'slug' => 'standard', 'color' => 'green']);
    }

    $userClass = config('community-manager.user_model');
    $this->joiner = $userClass::factory()->create();
    $this->community = createCommunity();
    $this->community->update(['join_id' => 'ABC12345', 'password' => 'secret']);
});

function joinWithCredentials($user)
{
    return Livewire::actingAs($user)
        ->test(JoinCommunity::class)
        ->set('join_id', 'ABC12345')
        ->set('password', 'secret')
        ->call('save');
}

it('admits the joiner at once when joining is open', function () {
    $this->community->update(['join_mode' => 'password']);

    joinWithCredentials($this->joiner)->assertHasNoErrors();

    expect($this->community->hasConfirmedMember($this->joiner->id))->toBeTrue()
        ->and($this->community->memberships()->where('user_id', $this->joiner->id)->first()->latestStatus()->reason)->toBe('open-self-join');
});

it('makes the joiner wait for an admin under approval, the default', function (?string $joinMode) {
    $this->community->update(['join_mode' => $joinMode]);

    joinWithCredentials($this->joiner)->assertHasNoErrors();

    $membership = $this->community->memberships()->where('user_id', $this->joiner->id)->first();

    expect($membership->start_at)->toBeNull()
        ->and($membership->latestStatus()->reason)->toBe('pending-self-join');
})->with(['approval' => 'approval', 'no mode set' => null]);

it('follows the configured default when the community has no join mode', function () {
    config()->set('member-manager.joining.default_mode', 'password');

    joinWithCredentials($this->joiner)->assertHasNoErrors();

    expect($this->community->hasConfirmedMember($this->joiner->id))->toBeTrue();
});

it('refuses the ID and password when the community is invite only', function () {
    $this->community->update(['join_mode' => 'invite_only']);

    joinWithCredentials($this->joiner)->assertHasErrors('form');

    expect($this->community->memberships()->where('user_id', $this->joiner->id)->exists())->toBeFalse();
});

it('refuses joining while the community says it is closed', function () {
    $closed = new class extends Community
    {
        protected $table = 'communities';

        public function isDisabled(): bool
        {
            return true;
        }
    };
    config()->set('community-manager.community_model', $closed::class);
    $this->community->update(['join_mode' => 'password']);

    joinWithCredentials($this->joiner)->assertHasErrors('form');

    expect($this->community->memberships()->where('user_id', $this->joiner->id)->exists())->toBeFalse();
});

it('lets someone whose request was turned down ask again', function () {
    $this->community->members()->attach($this->joiner->id, [
        'role_id' => Role::where('slug', 'member')->value('id'),
        'type_id' => Type::where('slug', 'standard')->value('id'),
        'start_at' => null,
        'end_at' => now()->subDay(),
    ]);

    joinWithCredentials($this->joiner)->assertHasNoErrors();

    $membership = $this->community->memberships()->where('user_id', $this->joiner->id)->first();

    expect($membership->start_at)->toBeNull()
        ->and($membership->end_at)->toBeNull()
        ->and($membership->latestStatus()->name)->toBe('rejoin-requested');
});

it('tells the person when their request is rejected, and an admin can confirm it after all', function () {
    Notification::fake();
    $userClass = config('community-manager.user_model');
    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);
    addCommunityMember($community, $owner);
    $owner->update(['current_community_id' => $community->id]);

    $community->members()->attach($this->joiner->id, [
        'role_id' => Role::where('slug', 'member')->value('id'),
        'type_id' => Type::where('slug', 'standard')->value('id'),
        'start_at' => null,
    ]);

    $page = Livewire::actingAs($owner)->test(MemberManagementPage::class, ['community_id' => $community->id])
        ->call('rejectMembership', $this->joiner->id);

    Notification::assertSentTo($this->joiner, MembershipRejected::class);
    expect($page->instance()->statusFilterOptions())->toHaveKey('rejected');

    $page->call('confirmMembership', $this->joiner->id);

    $membership = $community->memberships()->where('user_id', $this->joiner->id)->first();

    expect($membership->start_at)->not->toBeNull()
        ->and($membership->end_at)->toBeNull()
        ->and($community->hasConfirmedMember($this->joiner->id))->toBeTrue();
});
