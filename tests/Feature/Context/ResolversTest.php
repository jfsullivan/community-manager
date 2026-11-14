<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use jfsullivan\CommunityManager\Context\Resolvers\HostResolver;
use jfsullivan\CommunityManager\Context\Resolvers\RouteParameterResolver;
use jfsullivan\CommunityManager\Context\Resolvers\UserSelectionResolver;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Tests\User;

function siteCommunity(string $slug, ?string $customDomain = null): Community
{
    $community = createCommunity();
    $community->forceFill(['slug' => $slug, 'custom_domain' => $customDomain])->save();

    return $community;
}

describe('HostResolver', function () {
    beforeEach(function () {
        config()->set('community-manager.context.resolvers', [HostResolver::class]);
        config()->set('community-manager.hosts.base_domain', 'example.test');
        config()->set('community-manager.hosts.central_domains', ['example.test', 'www.example.test', 'platform.example.com']);
        config()->set('cache.default', 'array');
    });

    it('resolves {slug}.{base_domain}', function () {
        $acme = siteCommunity('acme');

        expect(app(HostResolver::class)->forHost('acme.example.test')?->is($acme))->toBeTrue()
            ->and(app(HostResolver::class)->forHost('ACME.Example.Test:8443')?->is($acme))->toBeTrue()
            ->and(app(HostResolver::class)->forHost('acme.example.test.')?->is($acme))->toBeTrue();
    });

    it('resolves nothing for central, reserved, nested or unknown hosts', function (string $host) {
        siteCommunity('acme');
        siteCommunity('www');

        expect(app(HostResolver::class)->forHost($host))->toBeNull();
    })->with([
        'base domain' => 'example.test',
        'central www' => 'www.example.test',
        'central elsewhere' => 'platform.example.com',
        'reserved label' => 'admin.example.test',
        'two labels' => 'x.acme.example.test',
        'unknown slug' => 'nobody.example.test',
        'invalid label' => '-acme.example.test',
        'other domain' => 'acme.example.org',
        'look-alike suffix' => 'acmeexample.test',
    ]);

    it('resolves a custom domain, case-insensitively', function () {
        $acme = siteCommunity('acme', 'Acme-HOA.org');

        expect(app(HostResolver::class)->forHost('acme-hoa.org')?->is($acme))->toBeTrue()
            ->and(app(HostResolver::class)->forHost('ACME-HOA.ORG')?->is($acme))->toBeTrue();
    });

    it('forgets the cached custom domains when one changes', function () {
        $acme = siteCommunity('acme', 'old.org');
        expect(app(HostResolver::class)->forHost('old.org')?->is($acme))->toBeTrue();

        $acme->forceFill(['custom_domain' => 'new.org'])->save();

        expect(app(HostResolver::class)->forHost('new.org')?->is($acme))->toBeTrue()
            ->and(app(HostResolver::class)->forHost('old.org'))->toBeNull();
    });

    it('keeps serving the cached map until it is forgotten', function () {
        $acme = siteCommunity('acme', 'old.org');
        expect(app(HostResolver::class)->forHost('old.org'))->not->toBeNull();

        // A write that skips model events doesn't drop the cache...
        DB::table('communities')->where('id', $acme->id)->update(['custom_domain' => 'new.org']);
        expect(app(HostResolver::class)->forHost('new.org'))->toBeNull();

        // ...until the app forgets it.
        HostResolver::forgetCustomDomains();
        expect(app(HostResolver::class)->forHost('new.org')?->is($acme))->toBeTrue();
    });

    it('turns subdomains or custom domains off when their settings are null', function () {
        siteCommunity('acme', 'acme.org');

        config()->set('community-manager.hosts.custom_domain_column', null);
        Cache::flush();
        expect(app(HostResolver::class)->forHost('acme.org'))->toBeNull();

        config()->set('community-manager.hosts.base_domain', null);
        expect(app(HostResolver::class)->forHost('acme.example.test'))->toBeNull();
    });

    it('makes the host\'s community current for a request', function () {
        $acme = siteCommunity('acme', 'acme.org');

        Route::middleware(['web', 'community.context'])->get('/_host', fn () => current_community()?->getKey() ?? 'none');

        $this->get('http://acme.example.test/_host')->assertSee((string) $acme->id);
        $this->get('http://acme.org/_host')->assertSee((string) $acme->id);
        $this->get('http://example.test/_host')->assertSee('none');
    });

    it('lets a signed-in user\'s selection win when listed first', function () {
        config()->set('community-manager.context.resolvers', [UserSelectionResolver::class, HostResolver::class]);
        $acme = siteCommunity('acme');
        $user = User::factory()->create();
        $own = createCommunity($user);
        $user->forceFill(['current_community_id' => $own->id])->save();

        Route::middleware('web')->get('/_both', fn () => current_community()?->getKey());

        actingAs($user)->get('http://acme.example.test/_both')->assertSee((string) $own->id);
        $this->app['auth']->forgetGuards();
        $this->get('http://acme.example.test/_both')->assertSee((string) $acme->id);
    });
});

describe('RouteParameterResolver', function () {
    beforeEach(function () {
        config()->set('community-manager.context.resolvers', [RouteParameterResolver::class]);
    });

    it('resolves a {community} route parameter by key', function () {
        $community = createCommunity();

        Route::middleware('web')->get('/_c/{community}', fn () => current_community()?->getKey() ?? 'none');

        $this->get('/_c/'.$community->id)->assertSee((string) $community->id);
        $this->get('/_c/999999')->assertSee('none');
    });

    it('matches the configured columns in order', function () {
        config()->set('community-manager.context.route_columns', ['slug']);
        $community = siteCommunity('acme');

        Route::middleware('web')->get('/_c/{community}', fn () => current_community()?->getKey() ?? 'none');

        $this->get('/_c/acme')->assertSee((string) $community->id);
    });

    it('uses a bound community model', function () {
        $community = createCommunity();

        Route::middleware(['web', 'community.context'])->get('/_bound/{community}', fn (Community $community) => current_community()?->getKey() ?? 'none');

        $this->get('/_bound/'.$community->id)->assertSee((string) $community->id);
    });
});
