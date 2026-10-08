# Changelog

All notable changes to `community-manager` will be documented in this file.

## v3.8.6 - Type filter applies on close - 2026-10-08

- The Transaction Type multi-select applies once, when its dropdown closes, instead of after a 500ms pause (needs apex-ui ^1.49.0). Ticking several types is one update however slowly you click.

## v3.8.5 - Type filter: one update for quick ticks - 2026-10-08

- The Transaction Type multi-select (transactions and member transaction history) waits 500ms after the last tick before updating, so ticking several types quickly is one update: no in-between result and no gap in the loading state.

## v3.8.4 - Transaction lists: 50 rows per update - 2026-10-08

- The transactions, member transactions and member transaction history pages render 50 rows per update (the usual page size) instead of 100, and load more as you scroll. Filter changes re-render about half as much. Member balances keeps 100.

## v3.8.3 - Accounting pages: stable modal keys - 2026-10-08

- The transactions, member transactions and member balances pages give their modals (and the member balance) explicit `wire:key`s. Livewire 4 otherwise derives a child's key from the loops rendered before it, so filtering the list re-keyed the modals; picking a second transaction type while the first update was still running broke the page ("Snapshot missing").

## v3.8.2 - Filter URLs update on apply - 2026-10-08

- List filters (transactions: type, method, period, pool, dates; member balances and status) now keep the URL in Livewire history mode: the address bar changes only once a filter is applied, not while a Filters-menu choice is still a draft. Each applied change becomes a Back-button step.

## v3.8.1 - Community news headings - 2026-10-07

### Changed

- **Community News page:** card headings read "Latest community news" / "Older community news" / "All community news", so they can't be mistaken for a pool's news. Requires article-manager **^3.13**.

## v3.8.0 - Multi-select transaction filters - 2026-10-07

### Changed

- **Transactions filters take several values:** **Type** on the community Transactions page and on a member's transaction history, and **Method** on the community Transactions page. Pick e.g. Deposit and Withdrawal together.
- The filters are lists in the URL (`?transactionTypeFilter[0]=deposit&transactionTypeFilter[1]=withdrawal`). **Old links still work:** a single value or an old numeric type id loads as a one-item list.
- New `transactionTypeSlugs()` and `methodFilterValues()` helpers. `transactionType()` still returns the type when exactly one is chosen.

## v3.7.6 - Transaction history card flush on phones - 2026-10-07

### Changed

- **Member transaction history on phones:** the history card sits right under the header, without the empty strip between them. Wider screens are unchanged.

## v3.7.5 - Transaction history balance centered on phones - 2026-10-07

### Changed

- **Member transaction history header on phones:** the Account Balance label and amount are centered, like the full-width button below them, and the amount is a size smaller. Wider screens are unchanged.

## v3.7.4 - Transaction history balance stat on phones - 2026-10-07

### Changed

- **Member transaction history header on phones:** the balance shows as a stat (a small "Account Balance" label over a larger amount, left-aligned above the buttons) instead of label and amount at opposite ends of a row. Wider screens are unchanged.

## v3.7.3 - Transaction history phone header - 2026-10-07

### Changed

- **Member transaction history header on phones:** the balance sits on its own row under the name and email (label left, amount right), and the action buttons span the full width, sharing it equally whether there's one button or several. Long names and emails truncate. Wider screens are unchanged.
- `member-balance-actions`: the wrapper is now `grid grid-flow-col auto-cols-fr md:w-auto md:flex` (was a fixed two-column grid). Apps that override this view should match it.

## v3.7.2 - Transaction history date range with presets - 2026-10-07

### Changed

- **Member transaction history:** the From and To date filters are now one **Dates** range field with presets (Last 7 Days, Last 30 Days, This Month, Last Month, This Year, Last Year). Presets use the viewer's own days, and the range still applies on the Filters menu's **Apply** button.

### Breaking (for apps that set the filter directly)

- `fromDate` / `toDate` are replaced by `dateRange` (`['start' => 'Y-m-d', 'end' => 'Y-m-d']`), including in the page URL.

## v3.7.1 - Pool filter option styling - 2026-10-07

### Changed

- **Pool filter options** on a member's transaction history: the pool name in the standard option size with the season (or tournament) as a smaller line underneath. Once a pool is chosen, the closed field shows just its name, so it's the same height as the other fields. Long names end in an ellipsis. Requires apex-ui **^1.46.1**.

## v3.7.0 - Accounting filters apply on Apply; pool seasons - 2026-10-07

### Changed

- **Filters menus apply on Apply** on the member transaction history, community **Transactions** and **Member Balances** pages. Requires apex-ui **^1.46**.
- **Pool filter options show which season or tournament a pool belongs to**, under its name, so same-named pools from different years can be told apart. Search matches either line.

### Breaking (for implementers of the Pool filter hook)

- `FiltersTransactionsByPool::poolOptions()` now returns `[poolId => ['name' => string, 'detail' => string|null]]`, newest first, instead of `[poolId => name]`.

## v3.6.5 - Transaction history filters - 2026-10-07

### Added

- **Filters on a member's transaction history:** **Type** up front, and **Pool**, **From** and **To** in the Filters menu, with "Clear all". Dates are whole days in the viewer's timezone.
- **Pool filter hook:** implement `jfsullivan\CommunityManager\Contracts\FiltersTransactionsByPool` (`poolOptions()` and `applyPoolFilter()`) and set `config('community-manager.transaction_pool_filter')` to it. Without it, the Pool filter is hidden.

### Fixed

- The page's search queried `users.name` without joining `users`, so searching errored. It now uses the transaction search (description, method, type) the admin Transactions page uses.

## v3.6.4 - Read-only member transaction history - 2026-10-07

### Changed

- **Member transaction history is read-only.** Edit/Delete row actions are gone from this page; admins edit and delete on the community admin **Transactions** page. Members no longer see a disabled menu on each row.
- **Header balance:** shows the member whose history it is (it showed the viewer's own balance), as plain text instead of a link back to the same page.
- **Balance links:** the `member-balance` component's link goes to the shown member's history, not always the viewer's.

### Added

- `x-community-manager::accounting.member-balance-actions` (Add Funds by default) beside the balance on a member's own history page. Host apps can override it to add their own actions, such as a payout request.

### Removed

- The unused `accounting.request-payout-button` component (it used a modal pattern that no longer exists).

## v3.6.3 - Transaction history page width - 2026-10-06

### Fixed

- **Member transaction history page:** the header and the Transaction History card now sit in the same centered column (`max-w-7xl`) as the community home and pool pages, instead of running edge to edge on wide screens.

## v3.6.2 - Role and type modals open instantly - 2026-10-05

The community member row passes each member's role, type and any blocked reason with its Change Role / Change Membership events, so those modals open without a request. Requires member-manager ^0.10.3.

## v3.6.1 - Say why a member can't be changed - 2026-10-05

`CommunityPolicy::manageMember` returns a reason with each denial, which member-manager's modals show:

- "Only the community owner can change or remove another admin."
- "The community owner's membership can't be changed here."

Requires member-manager ^0.10.2.

## v3.6.0 - Community admins run the community - 2026-10-05

**Community admins** (current members with the `admin` role) can now do everything the owner can, with three exceptions that stay with the owner:

- granting the admin role, and changing or removing another admin (or the owner);
- deleting the community;
- paying for the community (the host app gates the subscription on ownership).

What changed:

- **`CommunityPolicy`:**
  
  - `update`, `manage` and the member abilities now allow admins.
  - Adds member-manager's `manageMembers`, `manageMember` and `assignMemberRole` abilities.
  - Removes the old `before()` hook, which granted every ability to whoever owned the user's *current* community, whichever community was being checked.
  
- **`TransactionPolicy`:** allows admins as well as the owner.
  
- **`isCommunityAdmin()` and the `community-admin` middleware:** only a current admin membership counts. Pending, former and banned admins have no admin rights.
  
- **Security:**
  
  - `ResolvesCommunity::$community_id` is locked, so the browser can't point a page at another community.
  - Editing a transaction keeps it in its own community.
  

Requires member-manager ^0.10.

## v3.5.21 - Accounting pages on phones - 2026-10-02

**Transactions and Member Balances on phones:**

- The title shows a count.
- Add Transaction folds into a ⋮ menu.
- The list sits flush under the header with the grid's search and filter bar.

Requires apex-ui ^1.43.

## v3.5.20 - Slug-based transaction type filter - 2026-10-02

The Transaction Type filter uses slugs in the URL (`?transactionTypeFilter=deposit`) instead of ids. Old links with ids still work: they are converted to the slug on load.

## v3.5.19 - Clearable filter dropdowns - 2026-10-02

Filter dropdowns can be cleared: each now starts with an "Any …" option (apex-ui v1.40 `nullable` select). Requires apex-ui ^1.40.

## v3.5.18 - Accounting filters - one primary plus a Filters dropdown - 2026-10-01

Transactions and Member Balances filters follow the apex-ui v1.39 pattern: one primary dropdown in the header (**Type** / **Balance**), and the rest under the **Filters** dropdown with a count and "Clear all". Requires apex-ui ^1.39.

## v3.5.17 - Transactions and Member Balances filter row - 2026-10-01

### Transactions and Member Balances: filter row

- **Transactions:** a filter row with Type, Method and When (Last 7, 30 or 90 days, This year, Last year), plus Clear all and an "All transactions" heading.
- **Member Balances:** Balance and Members dropdowns, plus Clear all (new `clearBalanceFilters()`).

Requires apex-ui ^1.38.

## v3.5.16 - PHPStan - 2026-10-01

### PHPStan

PHPStan passes again:

- The transaction modals load the current community by `current_community_id` (new `ResolvesCurrentCommunity` concern) instead of the host app's `currentCommunity` relation, which the package can't see.
- The computed `community`, `user` and `transaction` properties are declared, and `Transaction` declares `community_id`.
- The member-status filter calls its `updatedMemberStatusFilter()` hook directly; the trait now provides a no-op default that pages override.

## v3.5.15 - Member Balances: one filter pattern - 2026-10-01

### Member Balances: one filter pattern

Member Balances uses apex-ui's shared `x-apex::filter-bar`:

- **Segments:** the balance (All / Positive / Negative / No balance).
- **Filters menu:** member status, still Current by default. When it's set to anything else, it shows as a removable "Members: Former" chip.

New `balanceFilterOptions()`, `memberStatusFilterChip()` and `clearMemberStatusFilter()` support this.

Requires apex-ui ^1.37.1.

## v3.5.14 - Community news in cards - 2026-10-01

### Community news in cards

The community news page now shows article-manager's shared card-style news list (Latest news / Older news) below the community navigation, instead of its own older copy of the list. Requires article-manager v3.11.3 for the card style.

## v3.5.13 - Balance card actions slot - 2026-10-01

### Balance card actions slot

`<x-community-manager::balance-card>` takes an optional `actions` slot. Its content renders beside **Add Funds**, so an app can add its own buttons, such as a member's **Request payout**. The action row also shows when the viewer can't add funds but the slot is filled.

## v3.5.12 - Balance card links to your transactions - 2026-10-01

### Balance card links to your transactions

The community balance card now has a **View transactions** link under the balance. It opens the member's own transaction history.

## v3.5.11 - Authorize transaction history and transaction modals - 2026-10-01

### Security: transaction history and transaction modals are authorized

- **Member transaction history** (`community.members.transactions`): only the member themself or the community's owner can view a member's history. Any other community member gets a 403. Before this, any member could open any other member's ledger by changing the user id in the URL. Admins use the admin accounting pages.
- **Delete transaction modal:** checks `delete-community-transaction` for the current community and only deletes transactions in that community. It's mounted on member-facing pages, and before this it would delete any transaction id it was opened with.
- **Edit transaction modal:** only loads and saves a transaction in the current community for someone with `edit-community-transaction`. A transaction can no longer be moved to another community through the form.
- **Create transaction modal:** checks `create-community-transaction` before saving.

## v3.5.10 - Buttons use x-apex::button - 2026-10-01

### Buttons use x-apex::button

The package's views use `x-apex::button` everywhere instead of `<flux:button>` directly, so buttons match the rest of the app: the same small size and outline icons. Behavior (variants, loading spinner, links) is unchanged.

## v3.5.9 - Transaction history without a nested card - 2026-09-30

### Member transaction history: no card inside a card

The member transaction history page wrapped its transactions grid (already an apex card) in the app's `x-app.card`, which drew a second frame around it. It also meant the package depended on a component that only exists in the host app.

The grid now sits in the same padded container as the other transaction pages, so the page no longer needs `x-app.card`.

## v3.5.8 - Empty states: nothing yet vs no results - 2026-09-30

### Empty states: "nothing yet" vs "no results"

List pages now tell the two apart:

- **Search or filters matched nothing:** "No {things} found. We couldn't find any {things} that meet that criteria." with a **Clear search** (or **Clear search and filters**) button.
- **Nothing here yet:** "No {things} yet" with a line saying there aren't any yet.

The member-balances list always reads as a search/filter miss (a pool or community always has members), with **Clear search and filters** resetting everything.

## v3.5.7 - Consistent empty states - 2026-09-30

### Empty states

Empty states no longer add their own `mt-6 mb-6` spacing; apex-ui v1.32 builds in standard spacing, so they match the rest of the app.

## v3.5.6 - Membership end is exclusive - 2026-09-28

### Fixed

- Current community members, the member status filter and the transaction form's member list treat a membership ending **now** as ended (`end_at > now()`), matching member-manager v0.9.54. A just-removed member no longer lingers as current for the rest of that second.

## v3.5.5 - Search by payment method - 2026-09-28

### Changed

- Transaction search also matches the payment method: a term found in a method label ("venmo", "pay" for PayPal) finds those deposits and withdrawals.

## v3.5.4 - Via method in the transaction column - 2026-09-28

### Changed

- Community transactions grid: the **Method** column is gone. Deposits and withdrawals with a method now lead the Transaction column with **Via Venmo** (etc.), and the description, if any, shows as a subline.

### Added

- `TransactionMethod::viaLabel()` — "Via Venmo"; `null` for Other.

## v3.5.3 - Page header spacing - 2026-09-27

### Fixed

- The community page header uses a flex gap instead of `space-y-6`, so filling the `actions` slot no longer makes the header taller on larger screens.

## v3.5.2 - Page header actions slot - 2026-09-27

### Added

- `<x-community-manager::page-header>` accepts an optional `actions` slot, rendered to the right of the community name (below it on phones), for page-level actions such as a member **Invite friends** button.

## v3.5.1 - Load more instead of pagination links - 2026-09-26

### Changed

- Paginated grids load more records as you scroll (apex-ui `<x-apex::grid.load-more>`, with a **Load more** button fallback) instead of showing numbered pagination links. Requires `jfsullivan/apex-ui` `^1.27`.

## v3.5.0 - Transaction payment method - 2026-09-26

### Added

- `TransactionMethod` enum (Venmo, PayPal, Zelle, Cash, Check, Other), stored in a new nullable `transactions.method` column and cast on `Transaction`.
- The transaction form shows a **Method** select for deposits and withdrawals. Other types always save `null`.
- The community transactions grid has a **Method** column on large screens.

### Upgrade

Add the column in the host app:

```php
Schema::table('transactions', fn (Blueprint $table) => $table->string('method')->nullable()->after('description'));
























```
## v3.4.13 - Member account panel as a transactions grid - 2026-09-25

- The member details Account card lists the ten most recent transactions as a date / transaction / amount grid that scrolls after about five rows.

## v3.4.12 - Card-style accounting grids and member account panel - 2026-09-25

- Community transactions, member balances and member transaction grids use apex-ui grid card mode.
- The member details Account panel is an `x-apex::card` with All Transactions in its header.

## v3.4.11 - Eight-digit Community IDs - 2026-09-25

### Eight-digit Community IDs

- `Community::newJoinId()` now draws an eight-digit ID (10000000–99999999, 90 million values), still re-drawing until unused.
- Existing IDs are unchanged.
- The factory matches.

## v3.4.10 - Unique six-digit Community IDs - 2026-09-25

### Unique six-digit Community IDs

- New `Community::newJoinId()` draws a random six-digit ID (100000–999999) and re-draws until no community uses it.
- **Fix:** `CommunityForm`'s generator retried on a collision but returned the colliding ID anyway. It now uses `newJoinId()`.
- `CreateCommunity` used an unchecked `mt_rand` up to eight digits. It now uses `newJoinId()`.
- The factory draws unique six-digit IDs, so apps can put a unique index on `communities.join_id`.

## v3.4.9 - From always stays on the app sending domain - 2026-09-18

### Changed

- `BaseMailable::envelope()` no longer uses the mail-template's `sender_email`
  as the From address. From is always `config('mail.from.address')` — mail
  providers (e.g. Resend) reject From addresses on domains the API key is not
  verified for. The template's `sender_name` stays as the display name and the
  template sender receives replies via Reply-To (omitted entirely when the
  template has no `sender_email`). Suite 72 passing.

Part of the BracketBrain Resend domain-authorization fix. Pairs with
member-manager v0.9.39 (`SendsOnBehalfOfInviter`), which covers
`CommunityInvitation`.

## v3.4.8 - Shorter Admin label on mobile - 2026-09-19

### v3.4.8 — Shorter Admin label on mobile

The community toolbar's admin tab now shows just **"Admin"** on narrow screens, expanding to the full **"Admin Tools"** from the `sm` breakpoint up, so the tab stays short on mobile.

- Responsive span inside the tab label (`Admin<span class="hidden sm:inline"> Tools</span>`); the `" Tools"` suffix is hidden below `sm`.
- Added `admin` and `admin-tools-suffix` keys to `resources/lang/en/labels.php` (existing `admin-tools` kept).

Refs jfsullivan/BracketBrain#91

## v3.4.7 - Uniform community toolbar across breakpoints - 2026-09-19

### v3.4.7 — Uniform community toolbar across breakpoints

The community toolbar now renders identically at every screen size — the desktop look everywhere.

**Changed**

- The toolbar item shows its icon inline beside the label with a single (desktop) text size at all breakpoints; the stacked-below-`sm` variant is gone.
- On small screens the tabs are equal width (`flex-1`) and spread evenly across the full row; from `sm` up they collapse to inline, left-aligned tabs (`w-auto`) with fixed spacing. Only the row distribution changes by breakpoint, never the item's internal layout.

Refs jfsullivan/BracketBrain#91

## v3.4.6 - Community toolbar visible on mobile - 2026-09-19

### v3.4.6 — Community toolbar visible on mobile

The community toolbar was hidden below the `sm` breakpoint, so mobile users saw no community tabs — only the hamburger drawer.

**Fix**

- The `page-header` navigation wrapper no longer applies `hidden ... sm:flex`; the toolbar now renders at every breakpoint.
- On mobile the tabs spread evenly across the full row (`justify-between w-full`) and collapse to inline, left-aligned tabs with fixed spacing from `sm` up — mirroring the pool toolbar. Non-admins have 3 tabs, admins 4 (consuming apps that add a Pools tab get 5).

The mobile drawer is unchanged and remains the only path to switch-community / balance / account actions.

Refs jfsullivan/BracketBrain#91

## v3.4.5 - Community toolbar icons - 2026-09-18

### Community toolbar icons

The community toolbar (`x-community-manager::navigation-menu`) now pairs each tab with an apex-ui icon, matching the app's pool dashboard toolbar (BracketBrain #91).

#### Added

- `navigation-menu.item` component: a toolbar tab that renders an apex-ui icon plus its label with selected/unselected styling, mirroring the pool toolbar item — the icon stacks above the label on mobile and sits beside it on `md+`.

#### Changed

- `navigation-menu` default view: rebuilt on the new `navigation-menu.item` component. Tabs (Dashboard, News, Members, Admin Tools) now show icons (`home`, `newspaper`, `users`, `settings`) and are driven by the `selected` prop with a route-based fallback. Stays pool-agnostic — only `community.*` routes appear; dead placeholder links (Documents/Calendar → `route('home')`) were dropped. Consuming apps add domain tabs (e.g. Pools) via a published override.

## v3.4.4 - Pool-agnostic package defaults - 2026-09-18

### Pool-agnostic package defaults

community-manager manages communities for any consuming app, including ones with no pools domain. This release removes the remaining pool awareness from the package defaults:

#### Changed

- `community-menu` default view: dropped the hard-coded Pools link (`route('pools.index')` would error in apps without that route). Consuming apps add their domain links via a published override.
- `header` default view: the community-context check now covers `community.*` routes only; apps whose domain pages also carry community context widen it via override.
- Community layout: removed a commented-out pools toolbar remnant.
- `create-community-page` default view: generalized the marketing copy (no pool-specific benefits).
- Doc comments in `MemberManagementPage` and `Community` generalized ("app-driven" instead of "pool-derived").

No PHP behavior changes. BracketBrain overrides all affected views, so its UI is unchanged.

## v3.4.3 - Community transaction delete actions - 2026-09-14

### Fixed

- **Delete Transaction is clickable again** on the Community Transactions page (BracketBrain #105): the menu item carried a stray hardcoded `disabled` attribute introduced during the apex-ui migration. The working per-member page item was never disabled; parity restored.
- **Bulk "Delete Selected Transactions"** added to the Community Transactions page (BracketBrain #104): the grid was `selectable` but had no `bulkActions` slot, so selection did nothing. Mirrors the member-transactions page; gated on `delete-community-transaction` (community owner). Tests cover row + bulk delete through the modal and owner/non-owner visibility.

## v3.4.2 - Config-overridable member management page - 2026-09-14

### Added

- The community admin members route now resolves its page class from `config('community-manager.components.member_management_page')` (default unchanged: `CommunityMemberManagementPage`), mirroring the existing `member_details_page` override so host apps can subclass the roster page (e.g. BracketBrain adds per-member pool participation).

## v3.4.1 - Transaction page sorting + search fixes - 2026-09-14

### Fixed

- **Community Transactions type sort no longer 500s** (BracketBrain #36): `CommunityTransactionsPage::transactionQuery()` was missing the `transaction_types` join that `setSort('type')` orders by (`Unknown column 'transaction_types.name'`). Both `transaction_types` and `users` are now joined.
- **Transaction search no longer 500s** (BracketBrain #106): `Transaction::scopeSearch` searched the dropped `users.name` column via `orWhereRelation('user', 'name', …)`. It now uses `searchByFullName()` (name_type-aware: first/last/CONCAT), so searching by member name works.
- **Member column now sorts** on the Community Transactions page: the blade's `sort-key="member"` had no `setSort()` case and silently fell through to the date sort. Sorts by member name (name_type-aware).
- **Transaction column now sorts** on both the Community and Member Transactions pages: `sort-key="transaction"` now orders by description.
- All sort/tiebreaker columns are table-qualified (`transactions.id` etc.) so the new joins can't make them ambiguous.

## v3.4.0 - Admin rename + lifecycle scoping - 2026-09-12

### Admin rename + lifecycle scoping

- `CommunityPolicy::renameCommunity` (owner or admin) + `Community::isCommunityAdmin()`; `UpdateCommunityName` action fixed and wired to it (host-app form wiring ships separately).
- Member Balances: status filter (Current/Pending/Former/Banned), defaults to Current.
- Transaction modal excludes former/banned members; roster shows Former/Banned badges.

## v3.4.0 - Admin rename ability, balances status filters, roster badges - 2026-09-12

### Added

- **`Community::isCommunityAdmin($user_id)`** — owner OR a current `admin`-role member, mirroring the `community-admin` route middleware so in-component authorization agrees with route access.
- **`CommunityPolicy::renameCommunity`** ability — authorizes the community owner and any `admin`-role member to rename the community (kept separate from owner-only `update`). The `UpdateCommunityName` action now authorizes via this ability. (Wiring a rename field into the host app's Community Settings page is an app-side follow-up.)
- **Member-status filter on the Member Balances page** (`MemberStatusFilter` trait: Current / Pending / Former / Banned segments). Defaults to **Current**, so former and banned members no longer appear on the balances page unless an admin selects their segment.

### Fixed

- **The Add/Edit Transaction modal member select no longer offers former or banned members.** `HasTransactionForm::usersSearchQuery()` now excludes ended and banned memberships (current and still-pending members remain selectable); editing keeps a now-former bound user visible via `withSelectedUserOption()`.
- **Community members roster shows a "Former" badge in the Role column** for ended memberships instead of the member's now-stale role (precedence Banned > Former > pending join-flow > live role).
- Fixed the stale `UpdateCommunityName` action namespace (was `jfsullivan\BrainTools\Actions`, unautoloadable) to `jfsullivan\CommunityManager\Actions`.

## v3.3.3 - Dark-mode variants across package views - 2026-09-11

Additive Tailwind `dark:` variants on 25 views (accounting pages/modals, admin dashboard, dropdown/profile/community menus, member rows, create-community, responsive nav), matching the BracketBrain app's dark-mode conventions: zinc surfaces/borders/text, translucent semantic tints, solid brand elements untouched. No light-mode classes changed (verified additive-only). No API/behavior changes.

## v3.3.2 - Allow article-manager v3 - 2026-09-07

Widens the jfsullivan/article-manager constraint to ^2.0||^3.0 so consumers can upgrade to article-manager v3.0.0 (comment approval removed). No code changes; the package only consumes ArticlePolicy and the Livewire page base classes, all unchanged in v3. Package suite verified green against v3.0.0 (48 passed).

## v3.3.1 - Ban + rejoin handling on the password join path - 2026-09-02

### Changed

`JoinCommunity` (id + password modal):

- Banned members are blocked ("You are unable to join that community.") until the ban is lifted.
- A **former** member re-requesting membership now reactivates their existing row as PENDING (`start_at`/`end_at` cleared, `rejoin-requested` appended to the status history) instead of being told "already a member". No duplicate rows.
- "Already a member" is now scoped to current memberships only.

## v3.3.0 - Banned members surface - 2026-09-02

### What's new

Builds on member-manager v0.9.22's ban primitives:

- The community members page gains a **Banned** status segment; banned rows show a red **Banned** badge and a **Lift Ban** action (other roster actions hidden while banned).
- Lifting a ban is gated like confirm/reject (`updateCommunityMember`).
- Requires `jfsullivan/member-manager` ^0.9.22.

## v3.2.1 - Join status instead of role for unconfirmed members - 2026-09-02

### Changed

The community member row now shows a member's join status in the Role column while their membership is unconfirmed (`start_at IS NULL`): **Invited** (amber) when they have an outstanding invitation, otherwise **Pending** (zinc). Confirmed members are unchanged. Mirrors the same change in member-manager v0.9.16's default row — this row overrides it and needed the same treatment.

Fully backward compatible; dependents on `^3.2` require no changes.

## v3.2.0 - Pending membership: confirmed-only gates, pending joins, confirm/reject UI - 2026-09-01

### What's new

Supports the app's invitation-management feature by making community access require a **confirmed** membership and adding an admin approval flow for pending members.

- **Confirmed-only access gates** — `EnsureIsCommunityMember` middleware and `CommunityPolicy::view` now require a confirmed membership (a membership row with `start_at` set), not merely an existing row. A pending membership (`start_at IS NULL`) no longer grants access.
- **Pending self-join** — the `JoinCommunity` modal's id+password join now creates a **pending** membership (`start_at NULL`, status `pending-self-join`) with awaiting-confirmation messaging and a pending-aware "already a member" guard.
- **`confirmedCommunities()`** relation on `HasCommunityMemberships`; `allCommunities()` and `belongsToCommunity()`/`switchCommunity()` are based on it. Raw `communities()` is unchanged for rosters/admin queries.
- **Confirm / reject roster actions** on the community `MemberManagementPage`: `confirmMembership()` (sets `start_at`, status `confirmed-by-admin`) and `rejectMembership()` (tombstone: `removed` status + `end_at`, row preserved so prior-removed members stay detectable), both gated by `updateCommunityMember`. A Pending roster segment and a member-row partial with Confirm/Reject actions are included.

### Compatibility

- Requires `jfsullivan/member-manager ^0.9` (uses the `isConfirmedMember` / `hasConfirmedMember` helpers from **v0.9.10**).
- **Behavior change:** any consumer relying on the middleware/policy granting access to a membership row with a null `start_at` must backfill `start_at` for existing members before upgrading, or those members will be treated as pending. (The BracketBrain app ships a backfill migration for this.)

## v3.1.1 - Laravel 13 support - 2026-08-27

Widen `illuminate/contracts` to allow Laravel 13 (`||^13.0`) and `brick/money` to allow 0.14 (Laravel 13 requires `brick/math` >= 0.14, which brick/money 0.8 cannot use). Migrate `Transaction` amount formatting off `Money::formatWith()` (removed in brick/money 0.14) to `MoneyNumberFormatter`.

## v3.1.0 - Restore 3.x versioning - 2026-08-27

The v0.8.0 and v0.8.1 releases below were accidentally numbered beneath v3.0.0, so version
constraints like `^3.0` resolved to v3.0.0 and skipped them. v3.1.0 re-releases that work
under the correct version line; it contains everything in v0.8.1 (no new changes). The
v0.8.x tags remain in place but should not be used.

### What's Changed (since v3.0.0 — the v0.8.0 + v0.8.1 content)

- **Member details page** (`community.admin.members.show`) — contact info, membership facts, status-history timeline, invited-by, and an Account panel (balance + recent transactions).
- **Invitations page** (`community.admin.members.invitations`) — pending invitations with resend/cancel.
- **Invite-first emails** — invitations send on behalf of the inviter (Reply-To), copy/paste URL fallback, signed-URL double-escaping fix.
- **Native sidebar flyout** for the mobile hamburger.
- **Transaction edit shows its user again** — selected user/transfer user always merged into the searchable select options.
- Community Transactions and Member Balances pages use shared `<x-apex::section-header>` and `<x-apex::button-group>` components.
- Requires `jfsullivan/member-manager` ^0.9 and `apex-ui` ^1.6.

## v0.8.1 - Transaction user select fix + shared header components - 2026-07-25

### What's Changed

- **Transaction edit shows its user again**: the searchable selects only load the first 20 alphabetical members, so a bound user outside that page rendered as empty. The selected user (and transfer user) is now always merged into the options. Regression-tested with 25+ members.
- Community Transactions and Member Balances pages use the shared `<x-apex::section-header>` with `size="sm" variant="primary"` action buttons.
- Member Balances filter group now uses `<x-apex::button-group>` (requires apex-ui ^1.6).

## v0.8.0 - Member details, invitations page, invite-first emails - 2026-07-24

### Community member experience overhaul

- **Member details page** (`community.admin.members.show`) — contact info, membership facts, status-history timeline, invited-by, and an Account panel (balance + recent transactions). Swappable via `community-manager.components.member_details_page`.
- **Invitations page** (`community.admin.members.invitations`) — pending invitations with resend/cancel, linked from the Member Management sidebar group.
- **Invite-first emails** — invitations send on behalf of the inviter (From stays on the app domain, Reply-To goes to the inviter); copy/paste URL fallback under buttons; signed-URL double-escaping fixed.
- **Native sidebar flyout** — the mobile hamburger now uses Flux's off-canvas sidebar (full height, full labels).
- Requires `jfsullivan/member-manager` ^0.9.

## v2.1.0 - Generic article policy - 2026-07-14

- Use article-manager's generic `ArticlePolicy` (`registerGates('community')`) instead of a bespoke `CommunityArticlePolicy`.
- Require `article-manager ^1.0` and `notifications ^1.0`; require `flux ^2.0`.
- Remove dead legacy article code (`ArticleController`, `Articles\Show`, the `CommunityArticlesIndexPage` that still contained a `dd('test')`, and the non-Livewire `articles/{index,show}` views).
- Fix the mobile admin sidebar reference and drop archived membership views.

**Full Changelog**: https://github.com/jfsullivan/community-manager/compare/v2.0.1...v2.1.0

## 2.1.0 - 2026-07-13

- Use article-manager's generic `ArticlePolicy` (`registerGates('community')`)
  instead of a bespoke `CommunityArticlePolicy`.
- Require `article-manager ^1.0` and `notifications ^1.0`; require `flux ^2.0`.
- Remove dead legacy article code (`ArticleController`, `Articles\Show`, the
  `CommunityArticlesIndexPage` that still contained a `dd('test')`, and the
  non-Livewire `articles/{index,show}` views).
- Fix the mobile admin sidebar reference and drop archived membership views.

## 2.0.0 - 2026-04-15

Updated for Laravel 11

## 1.0.3 - 2026-01-10

**Full Changelog**: https://github.com/jfsullivan/community-manager/compare/v1.0.2...v1.0.3

## 1.0.2 - 2025-09-16

Add Dynamic Factory Models

## 1.0.1 - 2025-09-11

Fix issue with missing description

## 1.0.0 - 2025-09-10

- Change logic for creating and updating transactions
- Added tests

**Full Changelog**: https://github.com/jfsullivan/community-manager/compare/v0.1.4...v1.0.0

## 0.1.4 - 2025-09-10

**Full Changelog**: https://github.com/jfsullivan/community-manager/compare/v0.1.3...v0.1.4

## 0.1.3 - 2025-09-04

Fix issue when the user doesn't have a current community, which occurs after initial account creation

## 0.1.2 - 2025-01-30

PWA Safe Area adjustment to prevent the navigation slide-over from overlapping with the IOS safe area on mobile devices.

## 0.1.1 - 2025-01-09

Fix money components

## 0.1.0 - 2024-12-13

Initial Release
