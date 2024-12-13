<?php

namespace jfsullivan\CommunityManager\Livewire\Forms;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Form;

class CommunityForm extends Form
{
    #[Rule('required|min:3|max:50', onUpdate: false)]
    public $name = '';

    #[Rule('required|string|max:255', onUpdate: false)]
    public $description = '';

    #[Rule('required|timezone', onUpdate: false)]
    public $timezone = '';

    #[Rule('boolean', onUpdate: false)]
    public $track_member_balances = false;

    public function store()
    {
        $communityModel = config('community-manager.community_model');

        $community = $communityModel::create([
            'name' => $this->name,
            'description' => $this->description,
            'join_id' => $this->generateUniqueJoinId(),
            'password' => Str::random(8),
            'timezone' => $this->timezone,
            'track_member_balances' => $this->track_member_balances,
            'user_id' => Auth::user()->id,
        ]);

        $ownerRole = config('member-manager.role_model')::where('slug', 'admin')->first();
        $membershipType = config('member-manager.type_model')::where('slug', 'standard')->first();

        $community->members()->attach(Auth::user()->id, [
            'role_id' => $ownerRole->id,
            'type_id' => $membershipType->id,
            'start_at' => now(),
        ]);

        return $community;
    }

    public function generateUniqueJoinId()
    {
        $join_id = random_int(100000, 999999);

        $communityModel = config('community-manager.community_model');

        if ($communityModel::where('join_id', $join_id)->exists()) {
            $this->generateUniqueJoinId();
        }

        return $join_id;

    }
}
