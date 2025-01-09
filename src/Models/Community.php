<?php

namespace jfsullivan\CommunityManager\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Mail\Templates\CommunityInvitationMailTemplate;
// use jfsullivan\ArticleManager\Traits\HasArticles;
// use jfsullivan\BrainTools\Mail\Templates\CommunityInvitationMailTemplate;
use jfsullivan\CommunityManager\Traits\ChecksForFeatures;
use jfsullivan\CommunityManager\Traits\TracksMemberBalances;
use jfsullivan\MemberManager\Traits\HasMembers;
use jfsullivan\MemberManager\Traits\InvitesMembers;

class Community extends Model
{
    use ChecksForFeatures;
    // use HasArticles;
    use HasFactory;
    use HasMembers;

    use InvitesMembers;
    use TracksMemberBalances;

    protected $fillable = ['name', 'user_id', 'join_id', 'password', 'track_member_balances', 'timezone'];

    public $invitationMailable = 'jfsullivan\CommunityManager\Mail\CommunityInvitation';

    /**************************************************************************
     * Model Factory
    ***************************************************************************/
    protected static function newFactory()
    {
        return \jfsullivan\CommunityManager\Database\Factories\CommunityFactory::new();
    }

    /**************************************************************************
     * Model Relationships
    ***************************************************************************/
    public function owner()
    {
        return $this->belongsTo(config('community-manager.user_model'), 'user_id');
    }

    public function memberships()
    {
        return $this->morphMany(Membership::class, 'model');
        // ->leftJoin('users', 'memberships.user_id', 'users.id')
        // ->withFullName();
    }

    /**************************************************************************
     * Model Scopes
    ***************************************************************************/
    public function scopeOwnedBy($query, User $user)
    {
        return $query->where('communities.user_id', $user->id);
    }

    public function scopeNotOwnedBy($query, $user_id)
    {
        return $query->where('communities.user_id', '!=', $user_id);
    }

    /**************************************************************************
     * Model Methods
    ***************************************************************************/
    public function allUsers()
    {
        return $this->users->merge([$this->owner]);
    }

    public function purge()
    {
        $this->owner()->where('current_community_id', $this->id)
            ->update(['current_community_id' => null]);

        $this->users()->where('current_community_id', $this->id)
            ->update(['current_community_id' => null]);

        $this->users()->detach();

        $this->delete();
    }

    public function isOwner($user_id)
    {
        return $this->user_id == $user_id;
    }

    // public function getInvitationModel()
    // {
    //     return CommunityInvitation::class;
    // }

    public function invitationMailTemplate()
    {
        return $this->morphOne(CommunityInvitationMailTemplate::class, 'model');
    }

    public function hasUserWithEmail(string $email)
    {
        return $this->allUsers()->contains(function ($user) use ($email) {
            return $user->email === $email;
        });
    }

    public function removeUser($user)
    {
        if ($user->current_community_id === $this->id) {
            $user->forceFill([
                'current_community_id' => null,
            ])->save();
        }

        $this->users()->detach($user);
    }

    protected function currency(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => 'USD',
        );
    }

    protected function initials(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => trim(collect(explode(' ', $attributes['name']))
                ->map(function ($segment) {
                    return mb_substr($segment, 0, 1);
                })->join('')),
        );
    }
}
