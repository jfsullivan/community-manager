<?php

use jfsullivan\ArticleManager\Livewire\Articles\Show;
use jfsullivan\CommunityManager\Context\Resolvers\UserSelectionResolver;
use jfsullivan\CommunityManager\Livewire\Dashboard;
use jfsullivan\CommunityManager\Livewire\Memberships\Pages\MemberDetailsPage;
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
     * The package's pages: /current-community, /communities/create and the
     * community.* member and admin pages. An app that only uses the
     * community core (context, isolation, membership modes) turns them off.
     */
    'routes' => [
        'enabled' => true,
    ],

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

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The ISO 4217 code member accounting uses for balances and transactions.
    |
    */

    'currency' => env('COMMUNITY_MANAGER_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Community Context
    |--------------------------------------------------------------------------
    |
    | The single "current community" for a request, job or command
    | (CommunityContext, Community::current(), current_community()).
    |
    | `resolvers`: tried in order on a request until one returns a community.
    |   UserSelectionResolver   the signed-in user's selected community
    |                           (users.current_community_id; users.community_id
    |                           in scoped_users mode).
    |   HostResolver            a custom domain, then {slug}.{base_domain}.
    |   RouteParameterResolver  a {community} route parameter.
    |
    | `lazy`: resolve on first use during a request, without the
    | community.context middleware. Switching tasks run only when a community
    | is set explicitly (the middleware, CommunityContext::run(), a job).
    |
    | `tasks`: classes implementing Contracts\SwitchesCommunity, run in order
    | when a community becomes current and in reverse when it is forgotten:
    |   Context\Tasks\ShareCommunityWithViews     View::share(view_key, ...)
    |   Context\Tasks\SetPermissionsTeam          spatie/laravel-permission team id
    |   Context\Tasks\RegisterCommunityViewPaths  per-community view and
    |                                             component paths (view_paths)
    |   Context\Tasks\SyncSpatieTenant            keep spatie/laravel-multitenancy's
    |                                             current tenant in step
    |
    */

    'context' => [
        'resolvers' => [
            UserSelectionResolver::class,
        ],

        'lazy' => true,

        'tasks' => [],

        'view_key' => 'currentCommunity',

        // A class implementing Contracts\ProvidesCommunityViewPaths, for RegisterCommunityViewPaths.
        'view_paths' => null,

        // The route parameter RouteParameterResolver reads, and the columns
        // it matches a scalar value against, in order.
        'route_parameter' => 'community',
        'route_columns' => ['id'],

        // UserSelectionResolver: only resolve a community the user belongs to
        // (any community for users passing site_admin_ability).
        'verify_membership' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Community Sites (HostResolver)
    |--------------------------------------------------------------------------
    |
    | `base_domain`: communities answer at {slug}.{base_domain} (one label,
    | lowercase). Null turns subdomains off.
    | `central_domains`: hosts that never resolve a community (marketing,
    | platform console). `reserved_subdomains`: labels never treated as a slug.
    | `slug_column` / `custom_domain_column`: communities columns (the
    | community-manager-site-migrations publish tag adds both); null turns
    | that lookup off. The custom-domain map is cached for `cache_ttl`
    | seconds and forgotten whenever a community's custom domain changes.
    |
    */

    'hosts' => [
        'base_domain' => env('COMMUNITY_BASE_DOMAIN'),
        'central_domains' => [],
        'reserved_subdomains' => ['www', 'admin', 'api', 'app', 'mail', 'platform', 'portal', 'static', 'assets'],
        'slug_column' => 'slug',
        'custom_domain_column' => 'custom_domain',
        'cache_key' => 'community-manager.custom-domains',
        'cache_ttl' => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Membership Mode
    |--------------------------------------------------------------------------
    |
    | `mode`: 'memberships' (the default): users are global and belong to many
    | communities through member-manager memberships. 'scoped_users': each
    | user belongs to one community (users.community_id; null = platform
    | staff), added by the community-manager-scoped-users-migrations tag.
    |
    | `checker`: the Contracts\ChecksMembership implementation; null picks the
    | one for the mode. `scoped_users.role_column`: an optional users column
    | holding a role slug; `admin_roles`: the slugs that count as admin.
    |
    */

    'membership' => [
        'mode' => 'memberships',
        'checker' => null,
        'scoped_users' => [
            'column' => 'community_id',
            'role_column' => null,
            'admin_roles' => ['admin'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Isolation (BelongsToCommunity)
    |--------------------------------------------------------------------------
    |
    | Only models using Traits\BelongsToCommunity are affected.
    |
    | `column`: the default community column. `when_no_community`: what a
    | scoped query returns with no current community: 'none' (fail closed)
    | or 'all'. `require_community_on_create`: refuse creating a row with no
    | community (unless the model allows shared rows). `guard_writes`: refuse
    | saving or deleting another community's row while one is current.
    |
    */

    'isolation' => [
        'column' => 'community_id',
        'when_no_community' => 'none',
        'require_community_on_create' => true,
        'guard_writes' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queues
    |--------------------------------------------------------------------------
    |
    | `propagate`: a job dispatched while a community is current runs inside
    | it. `aware_by_default`: false limits that to jobs implementing
    | Contracts\CommunityAware; Contracts\NotCommunityAware always opts out.
    |
    */

    'queue' => [
        'propagate' => true,
        'aware_by_default' => true,
    ],
];
