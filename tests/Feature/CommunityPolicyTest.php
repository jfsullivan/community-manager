<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

it('allows a community owner to access the community members admin page', function () {
    $userClass = config('community-manager.user_model');

    $communityOwner = $userClass::factory()->create();
    $community = createCommunity($communityOwner);

    addCommunityMember($community, $communityOwner);

    $communityMember = $userClass::factory()->create();
    addCommunityMember($community, $communityMember);

    actingAs($communityOwner)->get(route('community.admin.members.manage'))
        ->assertSuccessful();
});

it('prevents a community member to access the community members admin page', function () {
    $userClass = config('community-manager.user_model');

    $communityOwner = $userClass::factory()->create();
    $community = createCommunity($communityOwner);

    addCommunityMember($community, $communityOwner);

    $communityMember = $userClass::factory()->create();
    addCommunityMember($community, $communityMember);

    actingAs($communityMember)->get(route('community.admin.members.manage'))
        ->assertForbidden();
});

it('allows a community member to access the community dashboard page', function () {
    $userClass = config('community-manager.user_model');

    $communityOwner = $userClass::factory()->create();
    $community = createCommunity($communityOwner);

    addCommunityMember($community, $communityOwner);

    $communityMember = $userClass::factory()->create();
    addCommunityMember($community, $communityMember);

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
