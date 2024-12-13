<?php

namespace jfsullivan\BrainTools\Actions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class UpdateCommunityName
{
    public function update($user, $community, array $input)
    {
        Gate::forUser($user)->authorize('update', $community);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('updateCommunityName');

        $community->forceFill([
            'name' => $input['name'],
        ])->save();
    }
}
