# Changelog

All notable changes to `community-manager` will be documented in this file.

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
