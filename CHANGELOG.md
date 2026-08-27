# Changelog

All notable changes to `community-manager` will be documented in this file.

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
