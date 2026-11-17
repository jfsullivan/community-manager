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
  so nothing changes for them. `jfsullivan/member-manager` moved to `suggest` too:
  BracketBrain and BogeyBrain require it directly (keep it there), CompleteHOA
  doesn't use it.
- A `routes.enabled` switch (default on) for the package's pages.

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

## CompleteHOA: the core only, replacing apex-multitenancy (done)

CompleteHOA moved onto the core in one step and dropped apex-multitenancy and
spatie/laravel-multitenancy. What it uses, as a reference for an app with
community-scoped logins and host-based sites:

### 1. Community model and package pages

- `App\Models\Community` extends `Model` (not the package's `Community`) and uses
  `Traits\ActsAsCommunity`, so it needs no member-manager (which isn't installed):
  `Community::current()`, `isCurrent()`, `includesUser()`, `roleFor()`. Its own
  columns, relations, branding and templates are unchanged.
- Config: `community_model`, `user_model`, `community_policy` point at the app's
  classes; `routes.enabled => false` (CompleteHOA has its own `community.dashboard`
  and `invitations.accept` routes, and the package pages expect member-manager).
- `community()` and `tenant()` are app helpers returning `Community::current()`;
  `resolve_view()` / `resolve_layout()` moved into the app with its template resolver.

### 2. Hosts

```php
'context' => ['resolvers' => [HostResolver::class], 'lazy' => true],
'hosts' => [
    // copied from config/completehoa.php in AppServiceProvider::register()
    'base_domain' => config('completehoa.tenant_base_domain'),
    'central_domains' => CentralDomains::all(),
    'reserved_subdomains' => config('completehoa.reserved_subdomains'),
],
```

- `SetCommunityContext` is prepended to the `web` group, so every page (and Livewire
  update) resolves the community from the host after `TrustHosts` and `TrustProxies`
  (apex review R4). Lazy resolution stays on for code outside routes (error pages,
  Fortify's routes).
- The app's `tenant` group is `EnsureCommunityContext` (404 on the central site or an
  unknown host), an app `EnsureValidCommunitySession` (Spatie's session check, same
  session key) and the app's own site middleware.
- `TrustedHosts` lists `HostResolver::customDomains()` and forgets that cache now and
  after the transaction commits.

### 3. Switching tasks

`SetPermissionsTeam`, `ShareCommunityWithViews`, `RegisterCommunityViewPaths` (an app
`ProvidesCommunityViewPaths`: `templates/{template}/components` then
`templates/default/components`), plus two app tasks: `$currentTenant` /
`$currentTemplate` / `$currentTheme` view shares, and `URL::forceRootUrl()` at the
community's site outside HTTP. They replace `SetTenant`, `SyncApexTenantTask` and the
error page's own component registration; nothing leaks between communities (apex R3).

### 4. Community-scoped users

- `membership.mode => 'scoped_users'`; `membership.checker` is an app checker
  (login's `community_id`, admin = can use the admin tools in that community's
  permission team).
- `User` and `Person` use an app trait over `BelongsToCommunity` whose null rows are the
  platform's: platform staff sign in on every community's site, so their rows are
  shared (visible in every community) and may be saved there; deleting or moving them
  is still guarded. Community lists keep `User::scopeCommunityMembers()`.
  `User` also uses `BelongsToOneCommunity` (`isSuperAdmin()` is `isPlatformStaff()`).

### 5. Isolation

- Every model with a `community_id` uses `BelongsToCommunity` directly (the old app
  wrapper is gone). `Role` and `PositionTitle` set `$sharesCommunityRows` (system rows);
  everything else is strict (apex R1).
- `allTenants()` became `withoutCommunityScope()`, `allTenants()->where('community_id', $id)`
  became `forCommunity($id)`, `withoutGlobalScope('tenant')` became
  `withoutCommunityScope()`. Watch for a model's own static `forCommunity()` or
  `scopeForCommunity()`: the builder macro wins over a local scope.
- Factories default `community_id` to the current community when there is one (else a
  new community), so test setup never trips the write guard.
- `TenantIsolationTest` uses `CommunityIsolation::assertModelsAreScoped()` and
  `assertIsolated()` for properties, households, groups, articles, documents, events,
  announcements and pages.

### 6. Jobs and commands

- The package's queue payload carries the community; central jobs and mailables
  implement `NotCommunityAware` (read through Laravel's mailable/notification wrapper).
- Commands that loop communities and provisioning use `CommunityContext::run()`;
  `tenants:artisan` is an app alias for `community:run`.
