# Community Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jfsullivan/community-manager.svg?style=flat-square)](https://packagist.org/packages/jfsullivan/community-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/jfsullivan/community-manager.svg?style=flat-square)](https://packagist.org/packages/jfsullivan/community-manager)

Community Manager adds multi-tenant **communities** (organizations/groups) to a Laravel
app. A user can own communities and belong to them; each community has members, an
optional accounting ledger with per-member balances, news articles, and an admin area.
It ties together the sibling packages — [member-manager](https://github.com/jfsullivan/member-manager)
for memberships, [article-manager](https://github.com/jfsullivan/article-manager) for
news, and [notifications](https://github.com/jfsullivan/notifications) — behind a
community-scoped UI.

It is also the shared **community core** for BracketBrain, BogeyBrain and CompleteHOA:
one "current community" per request, job or command, community sites by host,
two membership modes, and strict opt-in data isolation. See
[Community context](#community-context), [Data isolation](#data-isolation) and
[ADOPTING.md](ADOPTING.md) for per-app adoption steps.

## Requirements

- PHP 8.4+
- Laravel 12 / 13
- Livewire 3 / 4 + `livewire/flux` ^2.0 and the [apex-ui](https://github.com/jfsullivan/apex-ui) stack
- Sibling packages: `jfsullivan/notifications`, `jfsullivan/user-timezone`,
  `jfsullivan/common-helpers`
- `jfsullivan/member-manager` for membership mode `memberships` (the default), the
  package's `Community` model and its member, invitation and accounting pages. Require
  it yourself (BracketBrain and BogeyBrain do); an app that only uses the community
  core in mode `scoped_users` (CompleteHOA) doesn't need it.
- Optional: `jfsullivan/article-manager` (community news pages; without it the article
  routes, links and gates are left out), `spatie/laravel-multitenancy` (keep Spatie's
  current tenant in step), `spatie/laravel-permission` (permission team per community)

## Installation

```bash
composer require jfsullivan/community-manager
```

Publish and run the migrations, then optionally the config/views:

```bash
php artisan vendor:publish --tag="community-manager-migrations"
php artisan migrate

php artisan vendor:publish --tag="community-manager-config"
php artisan vendor:publish --tag="community-manager-views"
```

## Setup

Add the traits to your `User` model — one for owning communities, one for belonging to
them:

```php
use jfsullivan\CommunityManager\Traits\OwnsCommunities;
use jfsullivan\CommunityManager\Traits\HasCommunityMemberships;

class User extends Authenticatable
{
    use OwnsCommunities;         // ownsCommunity($community), owned communities
    use HasCommunityMemberships; // the communities a user belongs to + current community
}
```

A community model (or your app's subclass of it) can additionally use
`HasTransactions` / `TracksMemberBalances` to enable the accounting ledger and
`ChecksForFeatures` for per-community feature flags.

## What it provides

- **Communities** — a `Community` model, creation flow, a member/admin dashboard, and a
  "current community" concept for multi-tenant scoping.
- **Members** — member management via member-manager, scoped to the community: the
  members list (filters popover, invite-first header), a member details page
  (`community.admin.members.show` — contact, status history, invited-by, balance +
  recent transactions, swappable via `components.member_details_page`), and a pending
  invitations page (`community.admin.members.invitations` with resend/cancel), all
  linked from the admin sidebar's Member Management section.
- **Accounting** — an optional transactions ledger with per-member balances
  (`brick/money`-backed amounts), member statements, and transaction admin.
- **Articles** — community news via article-manager, authorized with the shared
  `ArticlePolicy` (`registerGates('community')`).
- **Feature flags** — communities can toggle features (e.g. member-balance tracking).

Access is enforced with the `community-owner`, `community-admin`, and `community-member`
middleware.

## Extensibility

Three mechanisms, each with one job:

1. **Subclass an abstract Page** (from article-manager / member-manager) to reuse a
   screen with the community as owner. The `Livewire\Concerns\ResolvesCommunity` trait
   supplies `community_id` → `community()` → `owningModel()`; add `layout()` +
   `layoutProperties()` and you're done — never override `render()`. See
   `Livewire\Articles\Pages\*` and `Livewire\Memberships\Pages\{MemberManagementPage,
   MemberDetailsPage, InvitationManagementPage}`.
2. **Config component-swap** (`community-manager.components.*`) when an app replaces an
   entire screen wholesale (e.g. the dashboards) — routes resolve the class from config.
3. **Modal context via props** — member-manager's modals receive
   `owning_model_type` / `owning_model_id`; no subclassing needed (the `community`
   morph alias is registered by this package).

## Configuration

`config/community-manager.php` (key options):

```php
return [
    'user_model'             => \App\Models\User::class,
    'community_model'        => \App\Models\Community::class,
    'community_policy'       => \App\Policies\CommunityPolicy::class,
    'transaction_model'      => \App\Models\Transaction::class,
    'transaction_policy'     => \App\Policies\TransactionPolicy::class,
    'routes'                 => ['enabled' => true], // the package's pages; false for core-only apps
    'admin_layout'           => 'community-manager::components.layouts.admin',
    'components' => [
        'dashboard'           => /* Livewire component for the member dashboard */,
        'admin_dashboard'     => /* Livewire component for the admin dashboard */,
        'show_article'        => /* article show page component */,
        'member_details_page' => /* member details page (subclass to add app activity panels) */,
    ],
    'features'               => [ /* available feature flags */ ],
    'track_member_balances'  => true,
];
```

`currency` (default `USD`, env `COMMUNITY_MANAGER_CURRENCY`) sets the ISO code member
accounting uses. The `context`, `hosts`, `membership`, `isolation` and `queue` sections
are described below; every key has a default in code, so a config published before
they existed keeps working.

## Community context

`CommunityContext` holds the single current community for the request, job or command.

```php
use jfsullivan\CommunityManager\Facades\CommunityContext;
use jfsullivan\CommunityManager\Models\Community;

Community::current();          // ?Community (also current_community())
CommunityContext::id();        // its key, or null
$community->isCurrent();

CommunityContext::run($community, function () {   // a model, key or slug
    // ...current here; the previous community comes back afterwards, even on an exception
});
CommunityContext::runWithoutCommunity(fn () => ...);
CommunityContext::set($community);                // for the rest of the process
CommunityContext::forget();
```

**Resolvers** (`context.resolvers`, tried in order on a request):

| Resolver | Finds |
|---|---|
| `UserSelectionResolver` (default) | The signed-in user's selected community (`users.current_community_id`, switched by `PUT /current-community`); `users.community_id` in `scoped_users` mode. A community the user doesn't belong to resolves to none unless they pass `site_admin_ability` (`context.verify_membership`). |
| `HostResolver` | A central domain (`hosts.central_domains`) is none; `{slug}.{hosts.base_domain}` (one lowercase label, not in `hosts.reserved_subdomains`) by `hosts.slug_column`; otherwise `hosts.custom_domain_column`, from a cached domain map that is forgotten whenever a community's custom domain changes (`HostResolver::forgetCustomDomains()` for writes that skip model events). |
| `RouteParameterResolver` | A bound `{community}` route parameter, or its value matched against `context.route_columns`. |

Resolution is lazy by default (`context.lazy`): the first `Community::current()` in a
request runs the resolvers and the answer is remembered for that request (and
forgotten on login/logout). The `community.context` middleware resolves and sets the
community explicitly for the request instead, and `community.required` answers 404
when there is none. Both are Livewire persistent middleware.

**Switching tasks** (`context.tasks`) run whenever a community is set explicitly, and are
undone before the next switch so nothing leaks between communities in workers, Octane
or multi-community tests:

| Task | Does |
|---|---|
| `Context\Tasks\ShareCommunityWithViews` | `View::share(context.view_key, $community)` (`$currentCommunity`) |
| `Context\Tasks\SetPermissionsTeam` | `setPermissionsTeamId($community->id)` (spatie/laravel-permission teams) |
| `Context\Tasks\RegisterCommunityViewPaths` | View and anonymous-component paths from a `Contracts\ProvidesCommunityViewPaths` class in `context.view_paths` (e.g. a community's template folder) |
| `Context\Tasks\SyncSpatieTenant` | Makes the community spatie/laravel-multitenancy's current tenant (pair with `Integrations\Spatie\SyncCommunityContextTask` in `multitenancy.switch_tenant_tasks` for the other direction) |

Write your own by implementing `Contracts\SwitchesCommunity`.

**Queues.** A job dispatched while a community is current runs inside it (the key
travels in the payload); a job dispatched with none runs with none. Opt a job out with
`Contracts\NotCommunityAware`, or set `queue.aware_by_default` to false and opt jobs in
with `Contracts\CommunityAware`; either may also go on a queued mailable, notification,
event listener or broadcast event (Laravel's wrapper job is looked through).
`Jobs\Middleware\RunInCommunity($community)` pins a
job to a named community. If the community was deleted before the job ran, it runs
with none and logs a warning.

**Console.** `php artisan community:run acme "reports:send --force"` runs a command
inside a community (id or slug); `community:run all "..."` runs it for each community.

## Membership modes

`membership.mode`:

- `memberships` (default): global users belong to many communities through
  member-manager memberships (BracketBrain, BogeyBrain; pools keep working).
- `scoped_users`: each login belongs to one community (`users.community_id`, publish
  tag `community-manager-scoped-users-migrations`); null marks platform staff. Use
  `Traits\BelongsToOneCommunity` (`isPlatformStaff()`, `platformStaff()`,
  `communityLogins()`) and, for strict scoping, `BelongsToCommunity` with
  `protected string $whenNoCommunity = 'unowned';`.

Either way `Contracts\ChecksMembership` answers `isMember()`, `roleOf()` and `isAdmin()`
(also `$community->includesUser($user)` and `$community->roleFor($user)`). Bind your
own in `membership.checker` (e.g. spatie/laravel-permission roles).

**An app's own community model.** The core only needs
`config('community-manager.community_model')` to be an Eloquent model. One that doesn't
extend the package's `Community` (so it needs no member-manager) uses
`Traits\ActsAsCommunity` for `current()`, `isCurrent()`, `includesUser()` and
`roleFor()`, and sets `routes.enabled` to false so the package's pages (which expect
its model and memberships) aren't registered.

## Data isolation

Opt in per model with `Traits\BelongsToCommunity`; nothing changes for models without it.

```php
class Pool extends Model
{
    use BelongsToCommunity;

    // Optional:
    // protected string $communityColumn = 'community_id';
    // protected bool $sharesCommunityRows = true;    // rows with no community are system rows, visible everywhere
    // protected string $whenNoCommunity = 'unowned'; // 'none' (default), 'unowned' or 'all'
}
```

- **Strict scope:** queries see only the current community's rows. With no current
  community a scoped query returns nothing (`isolation.when_no_community`), so a
  forgotten context fails closed. Rows with no community are never shown unless the
  model shares them.
- **Auto-fill:** a new row takes the current community. With none current, creating a
  row with no community throws `MissingCommunityException` (set the column explicitly
  in seeders and central code).
- **Write guard:** while a community is current, saving, deleting or moving another
  community's row throws `CommunityMismatchException`. Deliberate cross-community
  writes go in `Pool::withoutCommunityGuard(fn () => ...)`.
- **On purpose:** `Pool::forCommunity($community)` (a model, key or list) and
  `Pool::withoutCommunityScope()`.
- Query-builder updates and deletes are scoped; `DB::table()`, `insert()` and
  `saveQuietly()` bypass model events, so keep those to trusted code.

**Testing helpers** (`jfsullivan\CommunityManager\Testing`):

```php
use jfsullivan\CommunityManager\Testing\CommunityIsolation;
use jfsullivan\CommunityManager\Testing\InteractsWithCommunityContext;

uses(InteractsWithCommunityContext::class); // setCurrentCommunity(), inCommunity(), forgotten after each test

it('scopes every model with a community column', function () {
    CommunityIsolation::assertModelsAreScoped(app_path('Models'), 'App\\Models', except: [Community::class]);
});

it('isolates pools', function () {
    // Give the row the community passed in (a factory making its own community would be refused).
    CommunityIsolation::assertIsolated(Pool::class, fn (Community $community) => Pool::factory()->create(['community_id' => $community->id]));
});
```

`assertIsolated()` proves a row made in one community can't be found, counted,
mass-updated, saved, deleted or moved from another, and that the escape hatches reach it.

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG](CHANGELOG.md).

## Credits

- [Jack Sullivan](https://github.com/jfsullivan)

## License

The MIT License (MIT). See [License File](LICENSE.md).
