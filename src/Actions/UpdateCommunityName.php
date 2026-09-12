<?php

namespace jfsullivan\CommunityManager\Actions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class UpdateCommunityName
{
    /**
     * Rename a community. Authorized via the `renameCommunity` ability (owner
     * or any `admin`-role member), not owner-only `update`.
     *
     * @param  array{name?: string}  $input
     */
    public function update($user, $community, array $input): void
    {
        Gate::forUser($user)->authorize('renameCommunity', $community);

        // Mirrors CommunityForm's create-time constraint for a consistent rule.
        Validator::make($input, [
            'name' => ['required', 'string', 'min:3', 'max:50'],
        ])->validateWithBag('updateCommunityName');

        $community->forceFill([
            'name' => $input['name'],
        ])->save();
    }
}
