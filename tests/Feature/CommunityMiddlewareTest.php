<?php

namespace jfsullivan\BrainTools\Tests\Feature;

it('redirects to home page when a user doesnt have a current community set', function () {
    $userClass = config('community-manager.user_model');

    createRoute('home', 'home');

    $communityOwner = $userClass::factory()->create();
    $community = createCommunity($communityOwner);

    addCommunityMember($community, $communityOwner);

    $communityMember = $userClass::factory()->create();
    addCommunityMember($community, $communityMember);

    $communityMember->current_community_id = null;
    $communityMember->save();

    actingAs($communityMember)->get(route('community.dashboard'))
        ->assertStatus(302)
        ->assertRedirectToRoute('home');
});
