<?php

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Route;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\User;
use jfsullivan\MemberManager\Models\Role;
use jfsullivan\MemberManager\Models\Status;

uses(LazilyRefreshDatabase::class);
uses(jfsullivan\CommunityManager\Tests\TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function actingAs(Authenticatable $user)
{
    return test()->actingAs($user);
}

function createRoute(string $name, string $uri)
{
    Route::get($uri, function () use ($name) {
        return $name;
    })->name($name);
}

function createCommunity(?User $user = null)
{
    $community = (empty($user))
            ? Community::factory()->create()
            : Community::factory()->create(['user_id' => $user->id]);

    return $community;
}

function addCommunityMember(Community $community, User $user)
{
    $memberRole = Role::where('slug', 'member')->first();
    $memberStatus = Status::where('slug', 'active')->first();

    $community->members()->attach($user->id, [
        'role_id' => $memberRole->id,
        'status_id' => $memberStatus->id,
    ]);

    $user->current_community_id = $community->id;

    return $community;
}

// function setupOrganizationMember(Organization $organization, User $user)
// {
//     $memberRole = Role::where('slug', 'member')->first();
//     $memberStatus = Status::where('slug', 'active')->first();

//     $organization->members()->attach($user->id, [
//         'role_id' => $memberRole->id,
//         'status_id' => $memberStatus->id
//     ]);

//     $user->current_organization_id = $organization->id;
//     $user->save();
// }
