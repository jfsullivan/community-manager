# Adopting the community core

community-manager is the shared community core for BracketBrain, BogeyBrain and
CompleteHOA. Everything added for that is **opt-in**: an app that upgrades and changes
nothing keeps its current behavior (current-community switching through
`users.current_community_id` and `PUT /current-community`, the `community.*` route
names, member-manager memberships including pools, the `community-owner`,
`community-admin`, `community-member` and `community-accounting` middleware aliases,
and every existing config hook).

What an upgrade does add, inertly:

- `Community::current()`, `current_community()` and the `CommunityContext` facade, which
  lazily answer with the signed-in user's selected community (when they belong to it).
- Two middleware aliases, `community.context` and `community.required`, used only where
  an app adds them.
- A queue payload key: a job dispatched while a community is current carries its key,
  and the worker makes it current while the job runs (only models using
  `BelongsToCommunity` and the configured switching tasks notice; none are configured
  by default).
- The `community:run` command.
- Requirements: PHP ^8.4 and Laravel ^12 || ^13 (both apps are on Laravel 13).
  `jfsullivan/article-manager` moved to `suggest`; all three apps require it directly,
  so nothing changes for them.

Read the [README](README.md) sections *Community context*, *Membership modes* and
*Data isolation* first.

---

## BracketBrain: nothing required

Upgrade (`composer update jfsullivan/community-manager`) and stop there if you like.

**Optional: strict isolation.** Add it model by model; each step is independently safe.

1. Add the arch test, listing today's unscoped models as exceptions so it passes, then
   shrink the list:

   ```php
   use jfsullivan\CommunityManager\Testing\CommunityIsolation;

   it('scopes every model with a community column', function () {
       CommunityIsolation::assertModelsAreScoped(app_path('Models'), 'App\\Models', except: [
           Community::class,
           // Pool::class, ContestResult::class, PayoutRequest::class, ...
       ]);
   });
   ```

2. Make sure a community is current wherever those models are used:
   - Requests: lazy resolution already follows `current_community_id`. Where
     `ApplyViewAsCommunity` changes the in-memory user's community, add the
     `community.context` middleware **after** it (in the web group) so the "view as"
     community is the one resolved. Site admins pass `site_admin_ability`, so the
     community they view as resolves even without a membership.
   - Jobs: those dispatched from a request inherit it. Jobs that work across
     communities (plan reminders) implement `NotCommunityAware`, or take the community
     and use `RunInCommunity` / `CommunityContext::run()`.
   - Commands and the scheduler: wrap per-community work in
     `CommunityContext::run($community, fn () => ...)`, or use
     `php artisan community:run all "..."`.
   - Site-admin pages (`/admin/*`) that list across communities use
     `Model::withoutCommunityScope()` or `Model::forCommunity($ids)`.

3. Add `use BelongsToCommunity;` to a model (start with `Pool`), remove its explicit
   `where('community_id', ...)` filters if you like (they stay correct), and add
   `CommunityIsolation::assertIsolated(Pool::class, fn ($community) => Pool::factory()->create(['community_id' => $community->id]));`.
   Moving a pool between communities (`MovePoolsIntoCommunity`) is a deliberate
   cross-community write: wrap it in `Pool::withoutCommunityGuard(...)`.

Optional cleanups unrelated to isolation: BracketBrain still runs its own copies of the
disabling middleware and actions (`disabling.enabled` is off); BogeyBrain uses the
package's.

---

## BogeyBrain: strict isolation, plus HostResolver for optional community sites

### 1. Strict isolation

Follow BracketBrain's steps 1 to 3 for every model with a `community_id`: `Season`,
`Event`, `Contest`, `Round`, `Tournament`, `CourseRequest`, `PlayerExternalId` and the
accounting tables. Notes:

- Sluggable models scoped with `extraScope(fn ($q) => $q->where('community_id', ...))`
  keep working; the global scope adds the same constraint.
- Casual rounds that post to the user's personal community are ordinary community
  rows: create them inside `CommunityContext::run($user->personalCommunity, ...)` or
  set `community_id` explicitly (an explicit value is accepted when it matches the
  current community, or when none is current).
- Clubs and Courses are global: leave them without the trait.
- `community-member` / `community-admin` stay your access checks. The scope isn't
  authorization; it's the backstop when a query forgets its `where`.

### 2. Optional community sites (HostResolver)

For communities that want a public site at `{slug}.bogeybrain.com` or their own domain:

1. `php artisan vendor:publish --tag=community-manager-site-migrations` and migrate
   (adds nullable unique `communities.slug` and `communities.custom_domain`). Fill
   `slug` for communities with a site (e.g. with spatie/laravel-sluggable, which the
   package already requires).
2. Config:

   ```php
   'context' => [
       // The signed-in app UI keeps following the user's selection; the public
       // site follows the host.
       'resolvers' => [HostResolver::class, UserSelectionResolver::class],
   ],
   'hosts' => [
       'base_domain' => env('COMMUNITY_BASE_DOMAIN', 'bogeybrain.com'),
       'central_domains' => ['bogeybrain.com', 'www.bogeybrain.com'],
   ],
   ```

   Put `HostResolver` first so a community host always wins, and keep the app UI on
   the central domain, where `HostResolver` resolves nothing and the user's selection
   applies. To keep the two strictly apart instead, leave `resolvers` as is and add
   `community.context` plus a per-route-group resolver order in your own middleware.
3. Register the site routes on the community hosts (e.g.
   `Route::domain('{community_host}')` or a host check) with `community.context` and
   `community.required` so an unknown host is a 404.
4. Trust only your hosts: `TrustHosts` with the central domains, `*.{base_domain}` and
   the active custom domains (`HostResolver::customDomains()` lists them, cached).
   Keep `SESSION_DOMAIN` unset (host-only) so sessions aren't shared across sites.
5. When a custom domain is set or removed through the model, the cached domain map
   is forgotten automatically; after a raw update call `HostResolver::forgetCustomDomains()`.

Templates, themes and database menus aren't part of this package. If BogeyBrain adopts
apex-multitenancy templates or apex-menu-builder menus later, point
`RegisterCommunityViewPaths` (`context.view_paths`) at the community's template folders.

---

## CompleteHOA: community-scoped users + HostResolver replacing apex-multitenancy

CompleteHOA already has strict-looking isolation through apex-multitenancy; the move
replaces that machinery with this package's, one layer at a time. Each step can ship
on its own.

### 1. Community model and context

- Make `App\Models\Community` extend `jfsullivan\CommunityManager\Models\Community`
  instead of `ApexMultitenancy\Models\Tenant`, keeping Spatie by using its traits:

  ```php
  class Community extends \jfsullivan\CommunityManager\Models\Community implements IsTenant
  {
      use ImplementsTenant;          // Spatie: makeCurrent(), execute(), current()
      use UsesMultitenancyConfig;
      // ...
  }
  ```

  The package's `current()` / `isCurrent()` signatures match Spatie's, so both
  traits coexist. The package's columns not in CompleteHOA's `communities` table
  (join settings, accounting, disabling) are only read by the features that use them;
  publish and run the migrations you need, or leave them.
- Config: `community_model => App\Models\Community::class`, `user_model => App\Models\User::class`.
- Bridge Spatie and the context both ways while both exist:
  - `multitenancy.switch_tenant_tasks`: add `Integrations\Spatie\SyncCommunityContextTask`
    (replaces `SyncApexTenantTask`'s state syncing).
  - `community-manager.context.tasks`:
    `[SyncSpatieTenant::class, ShareCommunityWithViews::class, SetPermissionsTeam::class, RegisterCommunityViewPaths::class]`,
    plus an app task for `URL::forceRootUrl()` outside HTTP (from `SyncApexTenantTask`).
  - `context.view_paths`: a `ProvidesCommunityViewPaths` returning
    `resources/views/templates/{slug}` then `templates/default` (and their
    `components` folders). This replaces `SetTenant`'s and `ErrorPage`'s component-path
    registration and resets between communities (the apex R3 bug).
- `community()` (119 calls) and `tenant()` (84) can both return `Community::current()`
  during the move; `current_community()` is the package's own name, so it doesn't
  collide with the app helper.

### 2. Hosts (replaces `CommunityTenantFinder`)

```php
'context' => ['resolvers' => [HostResolver::class]],
'hosts' => [
    'base_domain' => config('completehoa.tenant_base_domain'),
    'central_domains' => CentralDomains::all(),
    'reserved_subdomains' => [/* CommunityTenantFinder's list */],
    'slug_column' => 'slug',
    'custom_domain_column' => 'custom_domain',
],
```

Then set `multitenancy.tenant_finder` to null (so Spatie no longer resolves from the
raw Host header at boot) and replace the `tenant` middleware group with
`['community.context', 'community.required']` placed **after** `TrustHosts`, so the
tenant comes from the validated host (apex review R4). `TrustedHosts` can build its
custom-domain list from `HostResolver::customDomains()`; `CommunityDomain` provisioning
keeps copying the active domain into `communities.custom_domain`, which now forgets the
cache automatically.

### 3. Community-scoped users

- `membership.mode => 'scoped_users'` (CompleteHOA already has `users.community_id`;
  skip the migration tag).
- `User`: `use BelongsToCommunity, BelongsToOneCommunity;` with
  `protected string $whenNoCommunity = 'unowned';`. A community then sees only its own
  logins, central code (no community) sees only platform staff, and platform staff no
  longer appear inside communities: drop the `communityMembers()` workarounds.
  `isSuperAdmin()` can become `isPlatformStaff()`.
- Bind a `membership.checker` that answers roles from spatie/laravel-permission teams
  and group grants (`HasRolesThroughGroups`), so `includesUser()` / `roleFor()` agree
  with the app's policies.
- The customer portal, SSO tokens and per-login password broker stay app code.

### 4. Isolation (replaces `BelongsToTenant`)

- Point `App\Models\Traits\BelongsToCommunity` at the package trait (or swap the ~50
  `use` lines). Models that rely on shared null rows (`Role` and other system rows) set
  `protected bool $sharesCommunityRows = true;`; everything else becomes strict (apex
  R1).
- Replace `withoutGlobalScope('tenant')` (35) and `allTenants()` (61) with
  `withoutCommunityScope()`, and `allTenants()->where('community_id', $id)` (9) with
  `forCommunity($id)`.
- Expect the write guard (apex R7) to surface legitimate cross-community writes in
  provisioning and the legacy import: wrap those in `Model::withoutCommunityGuard()` or
  run them with no current community.
- Replace `TenantIsolationTest`'s reflection with
  `CommunityIsolation::assertModelsAreScoped()` (keep `UNSCOPED_COMMUNITY_MODELS` as
  `except`), and add `assertIsolated()` for the most sensitive models.

### 5. Jobs and commands

- Spatie's tenant-aware queue and the package's payload both carry the community; keep
  one. With the bridge, either works. Central jobs keep `NotTenantAware` and add
  `NotCommunityAware`.
- Commands that loop with `$community->execute(...)` keep working through the bridge,
  or move to `CommunityContext::run()` / `community:run all`.
- `ProvisionCommunityAction`'s save-and-restore becomes `CommunityContext::run()`.

### 6. Retire apex-multitenancy

Once nothing reads its singleton (`tenant()`, `template()`, `theme()`, `resolve_view()`),
remove the package and its `tenant` middleware group. Templates and palettes are
CompleteHOA's own (`app/Support/Templates`) and stay in the app; menus stay on
apex-menu-builder.
