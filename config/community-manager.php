<?php

use jfsullivan\ArticleManager\Livewire\Articles\Show;
use jfsullivan\CommunityManager\Livewire\Dashboard;
use jfsullivan\CommunityManager\Livewire\Memberships\Pages\MemberDetailsPage;
use jfsullivan\CommunityManager\Mail\MembershipRequestMessageMail;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;
use jfsullivan\CommunityManager\Models\User;
use jfsullivan\CommunityManager\Notifications\CommunityDisabled;
use jfsullivan\CommunityManager\Notifications\CommunityEnabled;
use jfsullivan\CommunityManager\Notifications\CommunityReenableDeclined;
use jfsullivan\CommunityManager\Notifications\CommunityReenableRequested;
use jfsullivan\CommunityManager\Notifications\MembershipRejected;
use jfsullivan\CommunityManager\Policies\CommunityPolicy;
use jfsullivan\CommunityManager\Policies\TransactionPolicy;
use jfsullivan\CommunityManager\Support\UnlimitedPremiumAccess;

return [
    /*
     * The fully qualified class name of the user model.
     */
    'user_model' => User::class,

    /*
     * The fully qualified class name of the community model.
     */
    'community_model' => Community::class,

    /*
     * The fully qualified class name of the community policy.
     */
    'community_policy' => CommunityPolicy::class,

    /*
     * The fully qualified class name of the community transaction model.
     */
    'transaction_model' => Transaction::class,

    /*
     * The fully qualified class name of the community transaction type model.
     */
    'transaction_type_model' => TransactionType::class,

    /*
     * The fully qualified class name of the community transaction policy.
     */
    'transaction_policy' => TransactionPolicy::class,

    /*
     * Optional: a class implementing
     * jfsullivan\CommunityManager\Contracts\FiltersTransactionsByPool, which
     * adds a Pool filter to a member's transaction history. Null hides it.
     */
    'transaction_pool_filter' => null,

    /*
     * The path to the admin layout for the community.
     */
    'admin_layout' => 'community-manager::components.layouts.admin',

    /**************************************************************************
     * Livewire Components
     *
     * These are the Livewire components that are used by Community Manager.
     * You can override these components and providing the new paths.
    ***************************************************************************/

    /*
     * The path to the community components.
     */
    'components' => [
        'dashboard' => Dashboard::class,
        'admin_dashboard' => jfsullivan\CommunityManager\Livewire\Admin\Dashboard::class,
        'show_article' => Show::class,
        'member_details_page' => MemberDetailsPage::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Optional Features
    |--------------------------------------------------------------------------
    |
    | Some of the features provided by Community Manager are optional. Here you
    | may specify which features you would like to use.
    |
    */

    'features' => [
        // 'manage-members' => false,
        'track_member_balances' => false, // pre-requisite: manage-members = true
    ],

    /*
    |--------------------------------------------------------------------------
    | Member Accounting
    |--------------------------------------------------------------------------
    |
    | `self_serve`: whether whoever creates a community may turn on member
    | balance tracking. When false, only users passing the site-admin ability
    | may, and the create form ignores the toggle for everyone else.
    |
    */

    'member_accounting' => [
        'self_serve' => true,
    ],

    /*
     * The Gate ability that identifies a site administrator, and the users
     * column that marks one (to find who to tell about re-enable requests).
     */
    'site_admin_ability' => 'manage-site',

    'site_admin_column' => 'is_site_admin',

    /*
    |--------------------------------------------------------------------------
    | Disabling Communities
    |--------------------------------------------------------------------------
    |
    | `enabled`: registers the community.disabled and unavailable/{kind}
    | pages and adds the lock (EnsureCommunityIsEnabled) and read-only
    | (EnsureCommunityIsWritable) middleware to the web group. Off by default.
    |
    | `register_middleware`: append those two to the end of the web group.
    | Turn it off to place them yourself (e.g. before a billing redirect) with
    | $middleware->web(append: [...]) in bootstrap/app.php.
    |
    | `community_routes` are locked while the community is disabled, except
    | `open_routes`. `write_only_routes` send site admins back with a warning.
    | `unavailable_kinds`: the {kind} values the unavailable page accepts.
    | `contact_view`: optional view at the bottom of the disabled page.
    |
    */

    'disabling' => [
        'enabled' => false,
        'register_middleware' => true,
        'community_routes' => ['community.*'],
        'open_routes' => ['community.disabled', 'community.membership-status'],
        'write_only_routes' => [
            'community.admin.articles.create',
            'community.admin.articles.edit',
            'community.admin.members.invitations',
        ],
        'unavailable_kinds' => ['community'],
        'contact_view' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Membership Status
    |--------------------------------------------------------------------------
    |
    | `enabled`: registers community/{community}/membership-status (named
    | community.membership-status) and sends a pending or turned-down request
    | there from the community-member middleware, instead of a 403. Off by
    | default. `mail`: the mailable a person's message to the admins uses
    | (new $class($sender, $community, $body, $state)).
    |
    */

    'membership_status' => [
        'enabled' => false,
        'mail' => MembershipRequestMessageMail::class,
        'daily_message_limit' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Paid Features
    |--------------------------------------------------------------------------
    |
    | `resolver`: a class implementing
    | jfsullivan\CommunityManager\Contracts\ResolvesPremiumAccess, which
    | decides Community::allowsFeature() / featureLimit() /
    | hasPremiumAccess(). The default allows every feature with no limits.
    |
    */

    'premium' => [
        'resolver' => UnlimitedPremiumAccess::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | The notification classes the package sends; apps swap in their own
    | (same constructor arguments) for their own wording or mail views.
    |
    */

    'notifications' => [
        // new $class(Community $community), to the person whose request was turned down.
        'membership_rejected' => MembershipRejected::class,

        // new $class(Community $community), to its owner and admins.
        'community_disabled' => CommunityDisabled::class,
        'community_enabled' => CommunityEnabled::class,
        'community_reenable_declined' => CommunityReenableDeclined::class,

        // new $class(Community $community, Authenticatable $requester), to the site admins.
        'community_reenable_requested' => CommunityReenableRequested::class,
    ],
];
