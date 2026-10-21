<?php

use Illuminate\Support\Facades\Mail;
use jfsullivan\CommunityManager\Livewire\Pages\MembershipStatusPage;
use jfsullivan\CommunityManager\Mail\MembershipRequestMessageMail;
use jfsullivan\MemberManager\Models\Role;
use jfsullivan\MemberManager\Models\Type;
use Livewire\Livewire;

beforeEach(function () {
    createRoute('home', 'home');

    $userClass = config('community-manager.user_model');
    $this->owner = $userClass::factory()->create();
    $this->community = createCommunity($this->owner);

    $this->requester = $userClass::factory()->create();
});

/** Give the requester a membership row with the given start/end. */
function membershipRequest($test, ?string $startAt = null, ?string $endAt = null): void
{
    $test->community->members()->attach($test->requester->id, [
        'role_id' => Role::where('slug', 'member')->value('id'),
        'type_id' => Type::where('slug', 'active')->value('id'),
        'start_at' => $startAt,
        'end_at' => $endAt,
    ]);

    $test->requester->update(['current_community_id' => $test->community->id]);
}

it('sends a pending request to the status page instead of a 403', function () {
    membershipRequest($this);

    actingAs($this->requester)->get(route('community.dashboard'))
        ->assertRedirect(route('community.membership-status', ['community' => $this->community->id]));
});

it('explains a pending request and lets the person message the admins', function () {
    Mail::fake();
    membershipRequest($this);

    Livewire::actingAs($this->requester)->test(MembershipStatusPage::class, ['community' => $this->community->id])
        ->assertSee('Waiting for approval')
        ->set('message', 'Could you confirm me?')
        ->call('sendMessage')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    Mail::assertQueued(MembershipRequestMessageMail::class, fn ($mail) => $mail->hasTo($this->owner->email)
        && $mail->hasReplyTo($this->requester->email)
        && $mail->requestState === 'pending');
});

it('limits messages to the admins each day', function () {
    Mail::fake();
    config()->set('community-manager.membership_status.daily_message_limit', 1);
    membershipRequest($this);

    $page = Livewire::actingAs($this->requester)->test(MembershipStatusPage::class, ['community' => $this->community->id]);

    $page->set('message', 'First')->call('sendMessage')->assertHasNoErrors();
    $page->set('message', 'Second')->call('sendMessage')->assertHasErrors('message');

    Mail::assertQueuedCount(1);
});

it('says when a request was turned down', function () {
    membershipRequest($this, endAt: now()->subDay()->toDateTimeString());

    Livewire::actingAs($this->requester)->test(MembershipStatusPage::class, ['community' => $this->community->id])
        ->assertSee('Request not approved');
});

it('sends a confirmed member into the community', function () {
    membershipRequest($this, startAt: now()->subDay()->toDateTimeString());

    Livewire::actingAs($this->requester)->test(MembershipStatusPage::class, ['community' => $this->community->id])
        ->assertRedirect(route('community.dashboard'));
});

it('sends someone with no membership home without naming the community', function () {
    Livewire::actingAs($this->requester)->test(MembershipStatusPage::class, ['community' => $this->community->id])
        ->assertRedirect(route('home'));
});
