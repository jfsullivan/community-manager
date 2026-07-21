<?php

namespace jfsullivan\MemberManager\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use jfsullivan\CommunityManager\Traits\ChecksForFeatures;
use jfsullivan\MemberManager\Models\Role;
use jfsullivan\MemberManager\Models\Type;
use Spatie\MailTemplates\Models\MailTemplate;

class CreateCommunity
{
    use ChecksForFeatures;

    public function create($user, array $input)
    {
        $communityClass = config('community-manager.community_model');
        Gate::forUser($user)->authorize('create', $communityClass::class);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255', 'unique:communities'],
        ])->validateWithBag('createCommunity');

        $user->switchCommunity($community = $user->ownedCommunities()->create([
            'name' => $input['name'],
            'join_id' => mt_rand(100000, 10000000),
            'password' => Str::random(mt_rand(6, 8)),
        ]));

        $memberRoleId = Role::where('slug', 'admin')->value('id');
        $memberTypeId = Type::where('slug', 'standard')->value('id');

        $community->members()->attach(auth()->user()->id, [
            'created_at' => Carbon::now(),
            'role_id' => $memberRoleId,
            'type_id' => $memberTypeId,
            'start_at' => Carbon::now(),
        ]);

        $systemMailTemplate = MailTemplate::find(1);

        $newMailTemplate = $systemMailTemplate->replicate();
        $newMailTemplate->created_at = Carbon::now();
        $newMailTemplate->updated_at = null;
        $newMailTemplate->model_id = $community->id;
        $newMailTemplate->save();

        return $community;
    }
}
