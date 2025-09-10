<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

it('allows a community owner to access the community members admin page', function () {

    $userClass = config('community-manager.user_model');

    $communityOwner = $userClass::factory()->create();
    $community = createCommunity($communityOwner);

    addCommunityMember($community, $communityOwner);

    // Set the current community for the owner
    $communityOwner->update(['current_community_id' => $community->id]);

    $communityMember = $userClass::factory()->create();
    addCommunityMember($community, $communityMember);

    actingAs($communityOwner)->get(route('community.admin.members.index'))
        ->assertSuccessful();
});

it('prevents a community member to access the community members admin page', function () {

    $userClass = config('community-manager.user_model');

    $communityOwner = $userClass::factory()->create();
    $community = createCommunity($communityOwner);

    addCommunityMember($community, $communityOwner);

    $communityMember = $userClass::factory()->create();
    addCommunityMember($community, $communityMember);

    actingAs($communityMember)->get(route('community.admin.members.index'))
        ->assertForbidden();
});

it('allows a community member to access the community dashboard page', function () {

    $userClass = config('community-manager.user_model');

    $communityOwner = $userClass::factory()->create();
    $community = createCommunity($communityOwner);

    addCommunityMember($community, $communityOwner);

    $communityMember = $userClass::factory()->create();
    addCommunityMember($community, $communityMember);

    // Set the current community for the member
    $communityMember->update(['current_community_id' => $community->id]);

    actingAs($communityMember)->get(route('community.dashboard'))
        ->assertSuccessful();
});

it('prevents a community member to access the community admin dashboard page', function () {

    $userClass = config('community-manager.user_model');

    $communityOwner = $userClass::factory()->create();
    $community = createCommunity($communityOwner);

    addCommunityMember($community, $communityOwner);

    $communityMember = $userClass::factory()->create();
    addCommunityMember($community, $communityMember);

    actingAs($communityMember)->get(route('community.admin.index'))
        ->assertForbidden();
});
