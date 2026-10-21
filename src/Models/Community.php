<?php

namespace jfsullivan\CommunityManager\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use jfsullivan\CommunityManager\Contracts\ResolvesPremiumAccess;
use jfsullivan\CommunityManager\Database\Factories\CommunityFactory;
// use jfsullivan\ArticleManager\Traits\HasArticles;
// use jfsullivan\BrainTools\Mail\Templates\CommunityInvitationMailTemplate;
use jfsullivan\CommunityManager\Mail\Templates\CommunityInvitationMailTemplate;
use jfsullivan\CommunityManager\Support\UnlimitedPremiumAccess;
use jfsullivan\CommunityManager\Traits\ChecksForFeatures;
use jfsullivan\CommunityManager\Traits\TracksMemberBalances;
use jfsullivan\MemberManager\Contracts\HasAdministrators;
use jfsullivan\MemberManager\Traits\HasMembers;
use jfsullivan\MemberManager\Traits\InvitesMembers;
use jfsullivan\MemberManager\Traits\ManagesJoining;

/**
 * @property string $name
 * @property int|null $user_id The owner.
 * @property bool $track_member_balances
 * @property int|null $low_balance_threshold Cents; members below it are reminded to add funds (null = off).
 * @property bool $require_funds_for_entry_fees
 * @property int $entry_fee_allowance_cents
 * @property string|null $join_mode A JoinMode value; null = member-manager.joining.default_mode.
 * @property string|null $member_invites A MemberInvites value; null = off.
 */
class Community extends Model implements HasAdministrators
{
    use ChecksForFeatures;

    // use HasArticles;
    use HasFactory;
    use HasMembers;
    use InvitesMembers;
    use ManagesJoining;
    use TracksMemberBalances;

    protected $fillable = [
        'name', 'description', 'user_id', 'join_id', 'password', 'track_member_balances', 'is_personal', 'timezone',
        'low_balance_threshold', 'require_funds_for_entry_fees', 'entry_fee_allowance_cents',
        'join_mode', 'member_invites',
    ];

    public $invitationMailable = 'jfsullivan\CommunityManager\Mail\CommunityInvitation';

    protected function casts(): array
    {
        return [
            'track_member_balances' => 'boolean',
            'is_personal' => 'boolean',
            'low_balance_threshold' => 'integer',
            'require_funds_for_entry_fees' => 'boolean',
            'entry_fee_allowance_cents' => 'integer',
        ];
    }

    /**
     * A random eight-digit Community ID no other community uses — what members
     * type, with the password, to join.
     */
    public static function newJoinId(): int
    {
        do {
            $joinId = random_int(10000000, 99999999);
        } while (static::where('join_id', $joinId)->exists());

        return $joinId;
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
     * receives. It exists only to own that user's own content, so the
     * shared-community management surfaces (members, invitations, transactions,
     * articles) are not available on it — those belong to "real"/premium shared
     * communities.
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

    /**
     * Whether the community may use a paid feature (e.g. 'messages'), as the
     * resolver in config('community-manager.premium.resolver') decides. The
     * default resolver allows everything.
     */
    public function allowsFeature(string $feature): bool
    {
        return $this->premiumAccess()->allows($this, $feature);
    }

    /**
     * The community's limit for the key (e.g. 'members'), or null for no
     * limit. Not named limit(): that would shadow the query builder's.
     */
    public function featureLimit(string $key): ?int
    {
        return $this->premiumAccess()->limit($this, $key);
    }

    /** Whether the community has its plan's premium tools: {@see allowsFeature()} 'premium'. */
    public function hasPremiumAccess(): bool
    {
        return $this->allowsFeature('premium');
    }

    protected function premiumAccess(): ResolvesPremiumAccess
    {
        return app(config('community-manager.premium.resolver', UnlimitedPremiumAccess::class));
    }

    /**
     * Disabled by a site admin. Never, until an app (or a later release)
     * adds disabling; apps that disable communities override this.
     */
    public function isDisabled(): bool
    {
        return false;
    }

    /**
     * Whether the user's lists and switchers leave the community out (e.g. a
     * disabled community, for its members). Never, by default.
     */
    public function isHiddenFrom(?Authenticatable $user): bool
    {
        return false;
    }

    /**
     * Why nobody can join (or be confirmed) right now, or null when they can:
     * a disabled community takes nobody new. Overrides ManagesJoining.
     */
    public function joinClosedReason(): ?string
    {
        return $this->isDisabled() ? 'This community isn\'t taking new members right now.' : null;
    }

    /** Why invitations can't be sent right now, or null when they can (a disabled community). */
    public function invitationsClosedReason(): ?string
    {
        return $this->isDisabled() ? 'This community isn\'t sending invitations right now.' : null;
    }

    /**************************************************************************
     * Member accounting
     *
     * Balances, Add Funds, payment methods and transactions. A community that
     * doesn't track member balances shows none of it.
    ***************************************************************************/

    /**
     * Whether this community does member accounting at all: behind the
     * `features.track_member_balances` flag, never for personal communities,
     * and only when the community turned tracking on.
     */
    public function tracksMemberAccounting(): bool
    {
        return (bool) config('community-manager.features.track_member_balances')
            && $this->isShared()
            && (bool) $this->track_member_balances;
    }

    /**
     * Whether members must have enough available balance to cover an entry
     * fee before they can be charged it. Only while the community tracks
     * member balances.
     */
    public function requiresFundsForEntryFees(): bool
    {
        return $this->tracksMemberAccounting() && (bool) $this->require_funds_for_entry_fees;
    }

    /**
     * Where funds are required for entry fees, how far below zero (in cents)
     * a member's available balance may go to cover one; 0 otherwise.
     */
    public function entryFeeAllowanceCents(): int
    {
        return $this->requiresFundsForEntryFees() ? max((int) $this->entry_fee_allowance_cents, 0) : 0;
    }

    /**
     * Money a member has reported paying that an admin hasn't confirmed yet,
     * in cents; counted as available for entry fees. None by default; apps
     * with reported payments override this.
     */
    public function pendingCreditsCents(Authenticatable $member): int
    {
        return 0;
    }

    /** What a member can spend here, in cents: their balance plus pending credits. */
    public function spendableBalanceCents(Authenticatable $member): int
    {
        return (int) $this->memberBalance($member) + $this->pendingCreditsCents($member);
    }

    /**
     * How much more (in cents) the member needs before a fee of $feeCents is
     * allowed, or 0 when they can be charged it: always 0 unless the community
     * requires funds for entry fees, then the fee less their spendable balance
     * and the community's allowance.
     */
    public function entryFeeShortfallCents(Authenticatable $member, int $feeCents): int
    {
        if ($feeCents <= 0 || ! $this->requiresFundsForEntryFees()) {
            return 0;
        }

        return max($feeCents - $this->spendableBalanceCents($member) - $this->entryFeeAllowanceCents(), 0);
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

    /**
     * Whether the user administers this community: the owner, or a current
     * (started, not ended) member holding the `admin` role. Mirrors the access rule enforced by the
     * `community-admin` route middleware so in-component authorization (which
     * middleware doesn't re-run on Livewire updates) can agree with it.
     */
    public function isCommunityAdmin($user_id): bool
    {
        if ($this->isOwner($user_id)) {
            return true;
        }

        return $this->memberships()
            ->where('memberships.user_id', $user_id)
            ->whereRelation('role', 'slug', 'admin')
            ->current()
            ->exists();
    }

    /**
     * The users who run this community: its owner and its current admin-role
     * members ({@see isCommunityAdmin()}), without duplicates. They are
     * instances of config('community-manager.user_model'); an app that reads
     * its own user attributes (->email) can narrow the type in its model:
     *
     * `/** @return Collection<int, User> *\/ public function administrators(): Collection { return parent::administrators(); }`.
     * @return Collection<int, Authenticatable>
     */
    public function administrators(): Collection
    {
        $userModel = config('community-manager.user_model');

        $adminIds = $this->memberships()
            ->whereRelation('role', 'slug', 'admin')
            ->current()
            ->pluck('memberships.user_id')
            ->push($this->user_id)
            ->filter()
            ->unique();

        return $userModel::query()->whereKey($adminIds->all())->get()->toBase()->values();
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
