<?php
 
 namespace jfsullivan\CommunityManager\Livewire\Forms;

use Livewire\Attributes\Rule;
use Livewire\Form;
 
class JoinCommunityForm extends Form
{
    #[Rule('required|min:5', onUpdate: false)]
    public $join_id = '';
 
    #[Rule('required|string', onUpdate: false)]
    public $password = '';
}