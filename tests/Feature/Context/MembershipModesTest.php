<?php

use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Route;
use jfsullivan\CommunityManager\Context\Resolvers\HostResolver;
use jfsullivan\CommunityManager\Contracts\ChecksMembership;
use jfsullivan\CommunityManager\Facades\CommunityContext;
use jfsullivan\CommunityManager\Membership\MembershipsChecker;
use jfsullivan\CommunityManager\Membership\ScopedUsersChecker;
use jfsullivan\CommunityManager\Tests\Fixtures\Models\ScopedUser;
use jfsullivan\CommunityManager\Tests\User;
use jfsullivan\MemberManager\Models\Role;
use jfsullivan\MemberManager\Models\Type;

describe('memberships mode (the default)', function () {
    it('binds the memberships checker', function () {
        expect(app(ChecksMembership::class))->toBeInstanceOf(MembershipsChecker::class);
    });

    it('answers membership, role and admin questions', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $admin = User::factory()->create();
        $pending = User::factory()->create();
        $former = User::factory()->create();
        $stranger = User::factory()->create();
        $community = createCommunity($owner);

        addCommunityMember($community, $member);
        addCommunityAdmin($community, $admin);
        $community->members()->attach($pending->id, ['role_id' => Role::where('slug', 'member')->value('id'), 'type_id' => Type::value('id'), 'start_at' => null]);
        $community->members()->attach($former->id, ['role_id' => Role::where('slug', 'member')->value('id'), 'type_id' => Type::value('id'), 'start_at' => now()->subYear(), 'end_at' => now()->subDay()]);

        expect($community->includesUser($owner))->toBeTrue()
            ->and($community->includesUser($member))->toBeTrue()
            ->and($community->includesUser($admin))->toBeTrue()
            ->and($community->includesUser($pending))->toBeFalse()
            ->and($community->includesUser($former))->toBeFalse()
            ->and($community->includesUser($stranger))->toBeFalse()
            ->and($community->includesUser(null))->toBeFalse()
            ->and($community->roleFor($owner))->toBe('owner')
            ->and($community->roleFor($member))->toBe('member')
            ->and($community->roleFor($admin))->toBe('admin')
            ->and($community->roleFor($stranger))->toBeNull()
            ->and(app(ChecksMembership::class)->isAdmin($owner, $community))->toBeTrue()
            ->and(app(ChecksMembership::class)->isAdmin($admin, $community))->toBeTrue()
            ->and(app(ChecksMembership::class)->isAdmin($member, $community))->toBeFalse();
    });

    it('keeps pools and other owning models out of a community\'s answers', function () {
        $user = User::factory()->create();
        $community = createCommunity();
        $other = createCommunity();
        addCommunityMember($other, $user);

        expect($community->includesUser($user))->toBeFalse();
    });
});

describe('scoped_users mode', function () {
    beforeEach(function () {
        $migration = include __DIR__.'/../../../database/migrations/add_community_id_to_users_table.php.stub';
        $migration->up();

        config()->set('community-manager.membership.mode', 'scoped_users');
    });

    it('binds the scoped-users checker', function () {
        expect(app(ChecksMembership::class))->toBeInstanceOf(ScopedUsersChecker::class);
    });

    it('treats a login as a member of its own community only', function () {
        $community = createCommunity();
        $other = createCommunity();

        $login = ScopedUser::withoutCommunityGuard(fn () => ScopedUser::create(['name' => 'Pat', 'email' => 'pat@example.test', 'community_id' => $community->id]));
        $staff = ScopedUser::create(['name' => 'Staff', 'email' => 'staff@example.test']);

        expect($community->includesUser($login))->toBeTrue()
            ->and($other->includesUser($login))->toBeFalse()
            ->and($community->includesUser($staff))->toBeFalse()
            ->and($staff->isPlatformStaff())->toBeTrue()
            ->and($login->isPlatformStaff())->toBeFalse()
            ->and($community->roleFor($login))->toBe('member');
    });

    it('reads the role and admin roles from config', function () {
        config()->set('community-manager.membership.scoped_users.role_column', 'name');
        config()->set('community-manager.membership.scoped_users.admin_roles', ['manager']);
        $community = createCommunity();

        $manager = ScopedUser::create(['name' => 'manager', 'email' => 'm@example.test', 'community_id' => $community->id]);
        $resident = ScopedUser::create(['name' => 'resident', 'email' => 'r@example.test', 'community_id' => $community->id]);

        expect(app(ChecksMembership::class)->isAdmin($manager, $community))->toBeTrue()
            ->and(app(ChecksMembership::class)->isAdmin($resident, $community))->toBeFalse()
            ->and($community->roleFor($resident))->toBe('resident');
    });

    it('scopes logins to the current community and platform staff to central code', function () {
        $a = createCommunity();
        $b = createCommunity();
        ScopedUser::create(['name' => 'A', 'email' => 'a@example.test', 'community_id' => $a->id]);
        ScopedUser::create(['name' => 'B', 'email' => 'b@example.test', 'community_id' => $b->id]);
        ScopedUser::create(['name' => 'Staff', 'email' => 'staff@example.test']);

        CommunityContext::run($a, fn () => expect(ScopedUser::pluck('name')->all())->toBe(['A']));
        CommunityContext::run($b, fn () => expect(ScopedUser::pluck('name')->all())->toBe(['B']));

        // (The communities' factory owners are logins with no community too.)
        $named = fn ($query) => $query->whereIn('name', ['A', 'B', 'Staff']);

        expect(ScopedUser::tap($named)->pluck('name')->all())->toBe(['Staff'])
            ->and(ScopedUser::withoutCommunityScope()->tap($named)->communityLogins()->count())->toBe(2)
            ->and(ScopedUser::withoutCommunityScope()->tap($named)->platformStaff()->count())->toBe(1);
    });

    it('resolves a login\'s own community, and the host\'s community for its site', function () {
        config()->set('community-manager.hosts.base_domain', 'example.test');
        config()->set('community-manager.context.resolvers', [HostResolver::class]);
        config()->set('auth.providers.users.model', ScopedUser::class);

        $a = createCommunity();
        $a->forceFill(['slug' => 'acme'])->save();
        $b = createCommunity();
        $b->forceFill(['slug' => 'other'])->save();
        $login = ScopedUser::create(['name' => 'A', 'email' => 'a@example.test', 'community_id' => $a->id]);

        Route::middleware(['web', 'community.context'])->get('/_who', fn () => response()->json([
            'community' => CommunityContext::id(),
            // Not auth()->id(): SessionGuard falls back to the session's id when no user is found.
            'user' => auth()->user()?->getKey(),
        ]));

        // On its own community's site the login is found...
        $this->actingAs($login)->getJson('http://acme.example.test/_who')
            ->assertJson(['community' => $a->id, 'user' => $login->id]);

        // ...and a session from another community's login finds nobody here.
        $this->app['auth']->forgetGuards();
        $this->withSession(['login_web_'.sha1(SessionGuard::class) => $login->id])
            ->getJson('http://other.example.test/_who')
            ->assertJson(['community' => $b->id, 'user' => null]);
    });
});
