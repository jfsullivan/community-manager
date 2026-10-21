<?php

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use jfsullivan\CommunityManager\Actions\Communities\DisableCommunityAction;

beforeEach(function () {
    Notification::fake();
    Gate::define('manage-site', fn ($user) => (bool) $user->is_site_admin);
    createRoute('home', 'home');

    $userClass = config('community-manager.user_model');
    $this->owner = $userClass::factory()->create();
    $this->member = $userClass::factory()->create();
    $this->siteAdmin = $userClass::factory()->create(['is_site_admin' => true]);

    $this->community = createCommunity($this->owner);
    addCommunityMember($this->community, $this->member);
    $this->member->save();
    $this->owner->update(['current_community_id' => $this->community->id]);
    $this->siteAdmin->update(['current_community_id' => $this->community->id]);

    app(DisableCommunityAction::class)->disable($this->community, 'Terms');
});

it('registers the disabled and unavailable pages', function () {
    expect(route('community.disabled', ['community_id' => 1]))->toEndWith('/community/1/disabled')
        ->and(route('unavailable', ['kind' => 'community']))->toEndWith('/unavailable/community');
});

it('locks a disabled community through the web group, as the provider registers it', function () {
    actingAs($this->member)->get(route('community.dashboard'))
        ->assertRedirect(route('unavailable', ['kind' => 'community']));

    actingAs($this->owner)->get(route('community.dashboard'))
        ->assertRedirect(route('community.disabled', ['community_id' => $this->community->id]));
});

it('keeps it read-only for site admins on pages that only add or send', function () {
    actingAs($this->siteAdmin)->get(route('community.admin.articles.create'))
        ->assertRedirect(route('community.admin.index'));
});
