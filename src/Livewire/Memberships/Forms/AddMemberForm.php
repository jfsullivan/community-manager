<?php
 
 namespace jfsullivan\CommunityManager\Livewire\Memberships\Forms;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Rule;
use Livewire\Form;
use Illuminate\Support\Str;
 
class AddMemberForm extends Form
{
    public $owningModel = null;

    #[Rule('required|string|max:255', onUpdate: false)]
    public $first_name = null;

    #[Rule('required|string|max:255', onUpdate: false)]
    public $last_name = null;

    #[Rule('required|email|max:255', onUpdate: false)]
    public $email = null;

    #[Rule('required|integer', onUpdate: false)]
    public $role_id = null;

    #[Rule('required|integer', onUpdate: false)]
    public $type_id = null;

    #[Rule('required|boolean', onUpdate: false)]
    public $send_invite = true;

    public function save($owningModel)
    {
        $userClass = config('community-manager.user_model');
        $roleClass = config('member-manager.role_model');
        $typeClass = config('member-manager.type_model');

        $user = $userClass::firstOrCreate([
            'email' => $this->email,
        ], [
            'first_name' => $this->first_name,
            'last_name' =>  $this->last_name,
            'password' => Hash::make(Str::password(10))
        ]);

        $memberRole = $roleClass::find($this->role_id);
        $memberType = $typeClass::find($this->type_id);

        $owningModel->members()->attach($user->id, [
            'role_id' => $memberRole->id,
            'type_id' => $memberType->id,
        ]);

        return $owningModel->members()->where('user_id', $user->id)->first();
    }
}