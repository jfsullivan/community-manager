<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use jfsullivan\CommunityManager\Context\CommunityContext as Context;
use jfsullivan\CommunityManager\Context\Tasks\SetPermissionsTeam;
use jfsullivan\CommunityManager\Context\Tasks\ShareCommunityWithViews;
use jfsullivan\CommunityManager\Contracts\SwitchesCommunity;
use jfsullivan\CommunityManager\Exceptions\CommunityNotFoundException;
use jfsullivan\CommunityManager\Facades\CommunityContext;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Tests\User;

class RecordingTask implements SwitchesCommunity
{
    public static array $log = [];

    public function makeCurrent(Model $community): void
    {
        static::$log[] = 'make:'.$community->getKey();
    }

    public function forgetCurrent(): void
    {
        static::$log[] = 'forget';
    }
}

beforeEach(function () {
    RecordingTask::$log = [];
});

it('has no current community by default', function () {
    expect(Community::current())->toBeNull()
        ->and(current_community())->toBeNull()
        ->and(CommunityContext::has())->toBeFalse()
        ->and(CommunityContext::id())->toBeNull();
});

it('runs a callback inside a community and restores the previous one', function () {
    $a = createCommunity();
    $b = createCommunity();

    CommunityContext::run($a, function (Community $current) use ($a, $b) {
        expect($current->is($a))->toBeTrue()
            ->and(Community::current()->is($a))->toBeTrue()
            ->and($a->isCurrent())->toBeTrue();

        CommunityContext::run($b, fn () => expect(Community::current()->is($b))->toBeTrue());

        expect(Community::current()->is($a))->toBeTrue();

        CommunityContext::runWithoutCommunity(fn () => expect(Community::current())->toBeNull());

        expect(Community::current()->is($a))->toBeTrue();
    });

    expect(Community::current())->toBeNull()
        ->and(CommunityContext::isExplicit())->toBeFalse();
});

it('restores the previous community when the callback throws', function () {
    $a = createCommunity();
    $b = createCommunity();

    CommunityContext::set($a);

    try {
        CommunityContext::run($b, fn () => throw new RuntimeException('boom'));
    } catch (RuntimeException) {
    }

    expect(Community::current()->is($a))->toBeTrue();
});

it('finds a community by key or slug and refuses an unknown one', function () {
    $community = createCommunity();
    $community->forceFill(['slug' => 'acme'])->save();

    CommunityContext::set($community->id);
    expect(CommunityContext::id())->toBe($community->id);

    CommunityContext::set('acme');
    expect(CommunityContext::id())->toBe($community->id);

    CommunityContext::set((string) $community->id);
    expect(CommunityContext::id())->toBe($community->id);

    expect(fn () => CommunityContext::set('nope'))->toThrow(CommunityNotFoundException::class);
    expect(fn () => CommunityContext::set(999999))->toThrow(CommunityNotFoundException::class);
});

it('runs switching tasks on each switch and undoes them before the next', function () {
    config()->set('community-manager.context.tasks', [RecordingTask::class]);

    $a = createCommunity();
    $b = createCommunity();

    CommunityContext::set($a);
    CommunityContext::set($a); // same community: nothing to redo
    CommunityContext::set($b);
    CommunityContext::set(null);
    CommunityContext::run($a, fn () => null);

    expect(RecordingTask::$log)->toBe([
        'make:'.$a->id,
        'forget', 'make:'.$b->id,
        'forget',
        'make:'.$a->id, 'forget',
    ]);
});

it('shares the current community with views and restores the earlier value', function () {
    config()->set('community-manager.context.tasks', [ShareCommunityWithViews::class]);
    View::share('currentCommunity', 'before');

    $a = createCommunity();
    $b = createCommunity();

    CommunityContext::set($a);
    expect(View::shared('currentCommunity')->is($a))->toBeTrue();

    CommunityContext::set($b);
    expect(View::shared('currentCommunity')->is($b))->toBeTrue();

    CommunityContext::forget();
    expect(View::shared('currentCommunity'))->toBe('before');
});

it('points permission teams at the current community and back', function () {
    config()->set('community-manager.context.tasks', [SetPermissionsTeam::class]);

    $a = createCommunity();

    setPermissionsTeamId('team-before');

    CommunityContext::run($a, fn () => expect(getPermissionsTeamId())->toBe($a->id));

    expect(getPermissionsTeamId())->toBe('team-before');
});

describe('lazy resolution from the signed-in user', function () {
    beforeEach(function () {
        Route::middleware('web')->get('/_context', fn () => response()->json([
            'id' => current_community()?->getKey(),
            'explicit' => app(Context::class)->isExplicit(),
        ]));
    });

    it('resolves the user\'s selected community', function () {
        $user = User::factory()->create();
        $community = createCommunity();
        addCommunityMember($community, $user);
        $user->save();

        actingAs($user)->getJson('/_context')->assertJson(['id' => $community->id, 'explicit' => false]);
    });

    it('resolves an owned community', function () {
        $user = User::factory()->create();
        $community = createCommunity($user);
        $user->forceFill(['current_community_id' => $community->id])->save();

        actingAs($user)->getJson('/_context')->assertJson(['id' => $community->id]);
    });

    it('ignores a selected community the user does not belong to', function () {
        $user = User::factory()->create();
        $community = createCommunity();
        $user->forceFill(['current_community_id' => $community->id])->save();

        actingAs($user)->getJson('/_context')->assertJson(['id' => null]);
    });

    it('lets a site admin resolve any community', function () {
        Gate::define('manage-site', fn ($user) => (bool) $user->is_site_admin);

        $user = User::factory()->create();
        $user->forceFill(['is_site_admin' => true])->save();
        $community = createCommunity();
        $user->forceFill(['current_community_id' => $community->id])->save();

        actingAs($user)->getJson('/_context')->assertJson(['id' => $community->id]);
    });

    it('trusts the selection when membership checks are off', function () {
        config()->set('community-manager.context.verify_membership', false);

        $user = User::factory()->create();
        $community = createCommunity();
        $user->forceFill(['current_community_id' => $community->id])->save();

        actingAs($user)->getJson('/_context')->assertJson(['id' => $community->id]);
    });

    it('resolves nothing for a guest or when lazy resolution is off', function () {
        $this->getJson('/_context')->assertJson(['id' => null]);

        config()->set('community-manager.context.lazy', false);
        $user = User::factory()->create();
        $community = createCommunity($user);
        $user->forceFill(['current_community_id' => $community->id])->save();

        actingAs($user)->getJson('/_context')->assertJson(['id' => null]);
    });
});

describe('middleware', function () {
    it('sets the community explicitly for the request (community.context) and restores it after', function () {
        config()->set('community-manager.context.tasks', [RecordingTask::class]);
        config()->set('community-manager.context.lazy', false);

        Route::middleware(['web', 'community.context'])->get('/_explicit', fn () => response()->json([
            'id' => current_community()?->getKey(),
            'explicit' => app(Context::class)->isExplicit(),
        ]));

        $user = User::factory()->create();
        $community = createCommunity($user);
        $user->forceFill(['current_community_id' => $community->id])->save();

        actingAs($user)->getJson('/_explicit')->assertJson(['id' => $community->id, 'explicit' => true]);

        expect(RecordingTask::$log)->toBe(['make:'.$community->id, 'forget'])
            ->and(CommunityContext::isExplicit())->toBeFalse();
    });

    it('answers 404 without a community (community.required)', function () {
        Route::middleware(['web', 'community.required'])->get('/_required', fn () => 'ok');

        $this->get('/_required')->assertNotFound();

        $user = User::factory()->create();
        $community = createCommunity($user);
        $user->forceFill(['current_community_id' => $community->id])->save();

        actingAs($user)->get('/_required')->assertOk();
    });
});

it('keeps current-community switching working as before', function () {
    $user = User::factory()->create();
    $a = createCommunity($user);
    $b = createCommunity($user);
    $user->forceFill(['current_community_id' => $a->id])->save();

    actingAs($user)->put(route('current-community.update'), ['community_id' => $b->id])
        ->assertRedirect(route('community.dashboard'));

    expect($user->fresh()->current_community_id)->toBe($b->id);
});
