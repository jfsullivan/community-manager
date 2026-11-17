<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use jfsullivan\CommunityManager\Facades\CommunityContext;
use jfsullivan\CommunityManager\Tests\User;
use jfsullivan\CommunityManager\Traits\ActsAsCommunity;

/** A community model of an app's own, not the package's (no member-manager). */
class PlainCommunity extends Model
{
    use ActsAsCommunity;

    protected $table = 'communities';

    protected $guarded = [];
}

it('registers none of the package pages when routes are off', function () {
    expect(Route::has('community.dashboard'))->toBeFalse()
        ->and(Route::has('current-community.update'))->toBeFalse()
        ->and(Route::has('communities.create'))->toBeFalse()
        ->and(Route::has('community.admin.members.index'))->toBeFalse();
});

it('answers the core questions for a community model that only uses ActsAsCommunity', function () {
    config()->set('community-manager.community_model', PlainCommunity::class);
    config()->set('community-manager.membership.mode', 'scoped_users');
    app('db')->connection()->getSchemaBuilder()->table('users', fn ($table) => $table->unsignedBigInteger('community_id')->nullable());

    $here = PlainCommunity::create(['name' => 'Here', 'join_id' => 1, 'password' => 'x', 'timezone' => 'UTC']);
    $there = PlainCommunity::create(['name' => 'There', 'join_id' => 2, 'password' => 'x', 'timezone' => 'UTC']);
    $member = User::forceCreate(['name' => 'Member', 'email' => 'member@example.com', 'community_id' => $here->id]);

    expect(PlainCommunity::current())->toBeNull();

    CommunityContext::run($here, function () use ($here, $there, $member) {
        expect(PlainCommunity::current()?->is($here))->toBeTrue()
            ->and($here->isCurrent())->toBeTrue()
            ->and($there->isCurrent())->toBeFalse()
            ->and($here->includesUser($member))->toBeTrue()
            ->and($there->includesUser($member))->toBeFalse()
            ->and($here->roleFor($member))->toBe('member')
            ->and($there->roleFor($member))->toBeNull();
    });

    expect(PlainCommunity::current())->toBeNull();
});
