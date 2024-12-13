<?php

namespace jfsullivan\CommunityManager\Tests\Traits;

use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Tests\User;
use jfsullivan\MemberManager\Models\Role;
use jfsullivan\MemberManager\Models\Status;

trait TestsCommunities
{
    public function setupCommunity(User $user = null)
    {
        // $this->community = Community::factory()->create([ 'user_id' => $user->id ]);
        $this->community = (empty($user)) ? Community::factory()->create() : Community::factory()->create(['user_id' => $user->id]);
    }

    public function setupCommunityMember(Community $community, User $user)
    {
        $memberRole = Role::where('slug', 'member')->first();
        $memberStatus = Status::where('slug', 'active')->first();

        $community->members()->attach($user->id, [
            'role_id' => $memberRole->id,
            'status_id' => $memberStatus->id
        ]);

        $user->current_community_id = $community->id;
        $user->save();
    }
}
