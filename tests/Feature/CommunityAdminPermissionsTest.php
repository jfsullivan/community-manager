<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use Illuminate\Support\Facades\Gate;
use jfsullivan\MemberManager\Models\Role;

/**
 * Community admins run the community like the owner, except for the admin
 * role (granting it, or changing/removing other admins) and deleting it.
 */
beforeEach(function () {
    $userClass = config('community-manager.user_model');

    $this->owner = $userClass::factory()->create();
    $this->community = createCommunity($this->owner);

    $this->admin = $userClass::factory()->create();
    addCommunityAdmin($this->community, $this->admin);

    $this->member = $userClass::factory()->create();
    addCommunityMember($this->community, $this->member);

    $this->adminRole = Role::where('slug', 'admin')->first();
    $this->memberRole = Role::where('slug', 'member')->first();
});

function membershipOf($community, $user)
{
    return $community->memberships()->with('role')->where('memberships.user_id', $user->id)->sole();
}

it('lets admins run the community like the owner', function () {
    foreach (['update', 'manage', 'manageMembers', 'updateCommunityMember', 'removeCommunityMember', 'inviteCommunityMember'] as $ability) {
        expect(Gate::forUser($this->admin)->allows($ability, $this->community))->toBeTrue($ability);
    }

    expect(Gate::forUser($this->admin)->allows('create-community-transaction', [$this->community]))->toBeTrue()
        ->and(Gate::forUser($this->member)->allows('manageMembers', $this->community))->toBeFalse();
});

it('keeps the admin role and deleting the community with the owner', function () {
    expect(Gate::forUser($this->admin)->allows('assignMemberRole', [$this->community, $this->adminRole]))->toBeFalse()
        ->and(Gate::forUser($this->admin)->allows('assignMemberRole', [$this->community, $this->memberRole]))->toBeTrue()
        ->and(Gate::forUser($this->owner)->allows('assignMemberRole', [$this->community, $this->adminRole]))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows('delete', $this->community))->toBeFalse()
        ->and(Gate::forUser($this->owner)->allows('delete', $this->community))->toBeTrue();
});

it('lets admins manage members but not other admins or the owner', function () {
    $otherAdmin = config('community-manager.user_model')::factory()->create();
    addCommunityAdmin($this->community, $otherAdmin);

    expect(Gate::forUser($this->admin)->allows('manageMember', [$this->community, membershipOf($this->community, $this->member)]))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows('manageMember', [$this->community, membershipOf($this->community, $otherAdmin)]))->toBeFalse()
        ->and(Gate::forUser($this->owner)->allows('manageMember', [$this->community, membershipOf($this->community, $otherAdmin)]))->toBeTrue();
});

it('gives a removed admin no admin rights', function () {
    $this->community->members()->updateExistingPivot($this->admin->id, ['end_at' => now()->subMinute()]);

    expect($this->community->fresh()->isCommunityAdmin($this->admin->id))->toBeFalse()
        ->and(Gate::forUser($this->admin)->allows('update', $this->community->fresh()))->toBeFalse();
});
