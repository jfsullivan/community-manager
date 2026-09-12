<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use jfsullivan\CommunityManager\Actions\UpdateCommunityName;
use jfsullivan\MemberManager\Models\Role;
use jfsullivan\MemberManager\Models\Type;

function addCommunityAdmin($community, $user)
{
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin', 'color' => 'purple']);

    $community->members()->attach($user->id, [
        'role_id' => $adminRole->id,
        'type_id' => Type::where('slug', 'active')->first()->id,
        'start_at' => now(),
    ]);

    $user->current_community_id = $community->id;
    $user->save();

    return $community;
}

it('lets the owner rename the community', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);

    (new UpdateCommunityName)->update($owner, $community, ['name' => 'Renamed By Owner']);

    expect($community->fresh()->name)->toBe('Renamed By Owner');
});

it('lets an admin-role member rename the community', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);

    $admin = $userClass::factory()->create();
    addCommunityAdmin($community, $admin);

    (new UpdateCommunityName)->update($admin, $community, ['name' => 'Renamed By Admin']);

    expect($community->fresh()->name)->toBe('Renamed By Admin');
});

it('forbids a plain member from renaming the community', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);

    $member = $userClass::factory()->create();
    addCommunityMember($community, $member);
    $member->save();

    expect(fn () => (new UpdateCommunityName)->update($member, $community, ['name' => 'Nope']))
        ->toThrow(AuthorizationException::class);

    expect($community->fresh()->name)->not->toBe('Nope');
});

it('validates the new name', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);

    expect(fn () => (new UpdateCommunityName)->update($owner, $community, ['name' => 'ab']))
        ->toThrow(ValidationException::class);
});

it('resolves the renameCommunity ability for owner and admin but not a member', function () {
    $userClass = config('community-manager.user_model');

    $owner = $userClass::factory()->create();
    $community = createCommunity($owner);

    $admin = $userClass::factory()->create();
    addCommunityAdmin($community, $admin);

    $member = $userClass::factory()->create();
    addCommunityMember($community, $member);
    $member->save();

    expect(Gate::forUser($owner)->allows('renameCommunity', $community))->toBeTrue();
    expect(Gate::forUser($admin)->allows('renameCommunity', $community))->toBeTrue();
    expect(Gate::forUser($member)->allows('renameCommunity', $community))->toBeFalse();

    expect($community->isCommunityAdmin($owner->id))->toBeTrue();
    expect($community->isCommunityAdmin($admin->id))->toBeTrue();
    expect($community->isCommunityAdmin($member->id))->toBeFalse();
});
