<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use jfsullivan\CommunityManager\Actions\Communities\DeclineCommunityReenableRequestAction;
use jfsullivan\CommunityManager\Actions\Communities\DisableCommunityAction;
use jfsullivan\CommunityManager\Actions\Communities\EnableCommunityAction;
use jfsullivan\CommunityManager\Actions\Communities\RequestCommunityReenableAction;
use jfsullivan\CommunityManager\Events\CommunityDisabled as CommunityDisabledEvent;
use jfsullivan\CommunityManager\Http\Middleware\EnsureCommunityIsEnabled;
use jfsullivan\CommunityManager\Livewire\Pages\DisabledCommunityPage;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Notifications\CommunityDisabled;
use jfsullivan\CommunityManager\Notifications\CommunityEnabled;
use jfsullivan\CommunityManager\Notifications\CommunityReenableDeclined;
use jfsullivan\CommunityManager\Notifications\CommunityReenableRequested;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
    Gate::define('manage-site', fn ($user) => (bool) $user->is_site_admin);

    $userClass = config('community-manager.user_model');
    $this->owner = $userClass::factory()->create();
    $this->member = $userClass::factory()->create();
    $this->siteAdmin = $userClass::factory()->create(['is_site_admin' => true]);

    $this->community = createCommunity($this->owner);
    addCommunityMember($this->community, $this->member);
    $this->member->save();
});

/** Turn on the opt-in disabling routes and middleware for this test. */
function enableDisablingRoutes(): void
{
    config()->set('community-manager.disabling.enabled', true);
    createRoute('home', 'home');
    require __DIR__.'/../../routes/disabling.php';
    app('router')->pushMiddlewareToGroup('web', EnsureCommunityIsEnabled::class);
    Route::getRoutes()->refreshNameLookups();
}

it('disables a community with a reason, tells its admins and fires an event', function () {
    Event::fake([CommunityDisabledEvent::class]);

    app(DisableCommunityAction::class)->disable($this->community, ' Terms violation ');

    $community = $this->community->fresh();

    expect($community->isDisabled())->toBeTrue()
        ->and($community->disabled_reason)->toBe('Terms violation');
    Notification::assertSentTo($this->owner, CommunityDisabled::class);
    Notification::assertNotSentTo($this->member, CommunityDisabled::class);
    Event::assertDispatched(CommunityDisabledEvent::class);
});

it('leaves personal communities alone', function () {
    $personal = Community::factory()->create(['is_personal' => true]);

    app(DisableCommunityAction::class)->disable($personal, 'No');

    expect($personal->fresh()->isDisabled())->toBeFalse();
});

it('locks a disabled community for members and its admins but not site admins, and hides it from members only', function () {
    app(DisableCommunityAction::class)->disable($this->community, 'Terms');
    $community = $this->community->fresh();

    expect($community->isLockedFor($this->member))->toBeTrue()
        ->and($community->isLockedFor($this->owner))->toBeTrue()
        ->and($community->isLockedFor($this->siteAdmin))->toBeFalse()
        ->and($community->isHiddenFrom($this->member))->toBeTrue()
        ->and($community->isHiddenFrom($this->owner))->toBeFalse()
        ->and($community->joinClosedReason())->not->toBeNull()
        ->and($community->invitationsClosedReason())->not->toBeNull()
        ->and($community->invitationAcceptDeadline())->not->toBeNull();
});

it('records a re-enable request for the site admins, who can decline or enable it', function () {
    app(DisableCommunityAction::class)->disable($this->community, 'Terms');

    app(RequestCommunityReenableAction::class)->request($this->community->fresh(), $this->owner, 'We fixed it.');
    Notification::assertSentTo($this->siteAdmin, CommunityReenableRequested::class);
    expect($this->community->fresh()->reenable_request_message)->toBe('We fixed it.');

    app(DeclineCommunityReenableRequestAction::class)->decline($this->community->fresh(), 'Not yet.');
    Notification::assertSentTo($this->owner, CommunityReenableDeclined::class);
    expect($this->community->fresh())
        ->reenable_requested_at->toBeNull()
        ->reenable_decline_message->toBe('Not yet.');

    app(EnableCommunityAction::class)->enable($this->community->fresh());
    Notification::assertSentTo($this->owner, CommunityEnabled::class);
    expect($this->community->fresh())
        ->isDisabled()->toBeFalse()
        ->reenable_decline_message->toBeNull();
});

it('sends members to the unavailable page and admins to the disabled page', function () {
    enableDisablingRoutes();
    app(DisableCommunityAction::class)->disable($this->community, 'Terms');
    $this->owner->update(['current_community_id' => $this->community->id]);

    actingAs($this->member)->get(route('community.dashboard'))->assertRedirect(route('unavailable', ['kind' => 'community']));
    actingAs($this->owner)->get(route('community.dashboard'))->assertRedirect(route('community.disabled', ['community_id' => $this->community->id]));
});

it('shows the disabled page to the people who run the community and takes their request', function () {
    enableDisablingRoutes();
    app(DisableCommunityAction::class)->disable($this->community, 'Terms violation');

    Livewire::actingAs($this->owner)->test(DisabledCommunityPage::class, ['community_id' => $this->community->id])
        ->assertSee('Terms violation')
        ->set('message', 'We fixed it.')
        ->call('requestReenable')
        ->assertHasNoErrors();

    expect($this->community->fresh()->reenable_request_message)->toBe('We fixed it.');

    Livewire::actingAs($this->member)->test(DisabledCommunityPage::class, ['community_id' => $this->community->id])
        ->assertRedirect(route('unavailable', ['kind' => 'community']));
});
