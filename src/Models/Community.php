<?php

namespace jfsullivan\CommunityManager\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Database\Factories\CommunityFactory;
// use jfsullivan\ArticleManager\Traits\HasArticles;
// use jfsullivan\BrainTools\Mail\Templates\CommunityInvitationMailTemplate;
use jfsullivan\CommunityManager\Mail\Templates\CommunityInvitationMailTemplate;
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

    protected $fillable = ['name', 'user_id', 'join_id', 'password', 'track_member_balances', 'is_personal', 'timezone'];

    public $invitationMailable = 'jfsullivan\CommunityManager\Mail\CommunityInvitation';

    protected function casts(): array
    {
        return [
            'track_member_balances' => 'boolean',
            'is_personal' => 'boolean',
        ];
    }

    /**************************************************************************
     * Model Factory
    ***************************************************************************/
    protected static function newFactory()
    {
        return CommunityFactory::new();
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
        return $this->morphMany(config('member-manager.membership_model'), 'model');
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

    public function scopePersonal($query)
    {
        return $query->where('communities.is_personal', true);
    }

    public function scopeShared($query)
    {
        return $query->where('communities.is_personal', false);
    }

    /**************************************************************************
     * Feature gating
     *
     * A personal community is the private, auto-provisioned default every user
     * receives. It exists only to own that user's pools, so the shared-community
     * management surfaces (members, invitations, transactions, articles) are not
     * available on it — those belong to "real"/premium shared communities.
    ***************************************************************************/
    public function isPersonal(): bool
    {
        return (bool) $this->is_personal;
    }

    public function isShared(): bool
    {
        return ! $this->isPersonal();
    }

    public function canManageMembers(): bool
    {
        return $this->isShared();
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
