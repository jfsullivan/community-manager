<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use Illuminate\Support\Facades\Gate;
use jfsullivan\CommunityManager\Livewire\Pages\CreateCommunityPage;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\MemberManager\Models\Role;
use jfsullivan\MemberManager\Models\Type;
use Livewire\Livewire;

beforeEach(function () {
    createRoute('home', '/home');

    Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin', 'color' => 'purple']);
    Type::firstOrCreate(['slug' => 'standard'], ['name' => 'Standard', 'color' => 'gray']);

    $userClass = config('community-manager.user_model');
    $this->user = $userClass::factory()->create();
});

function createCommunityAs($user, bool $trackMemberBalances = false): Community
{
    actingAs($user);

    Livewire::test(CreateCommunityPage::class)
        ->set('form.name', 'Tuesday Night League')
        ->set('form.description', 'Nine holes every Tuesday.')
        ->set('form.timezone', 'America/New_York')
        ->set('form.track_member_balances', $trackMemberBalances)
        ->call('save')
        ->assertHasNoErrors();

    return Community::where('name', 'Tuesday Night League')->sole();
}

it('saves the description', function () {
    $community = createCommunityAs($this->user);

    expect($community->description)->toBe('Nine holes every Tuesday.');
});

it('lets anyone turn on balance tracking when it is self-serve', function () {
    $community = createCommunityAs($this->user, trackMemberBalances: true);

    expect($community->track_member_balances)->toBeTrue();
});

it('ignores the balance-tracking toggle from non-site-admins when it is not self-serve', function () {
    config()->set('community-manager.member_accounting.self_serve', false);
    Gate::define('manage-site', fn () => false);

    $community = createCommunityAs($this->user, trackMemberBalances: true);

    expect($community->track_member_balances)->toBeFalse();
});

it('lets site admins turn on balance tracking when it is not self-serve', function () {
    config()->set('community-manager.member_accounting.self_serve', false);
    Gate::define('manage-site', fn () => true);

    $community = createCommunityAs($this->user, trackMemberBalances: true);

    expect($community->track_member_balances)->toBeTrue();
});

it('hides the balance-tracking toggle from users who cannot use it', function () {
    config()->set('community-manager.member_accounting.self_serve', false);
    Gate::define('manage-site', fn () => false);
    actingAs($this->user);

    Livewire::test(CreateCommunityPage::class)
        ->assertDontSee('Enable member balance tracking');
});
