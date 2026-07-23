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

## Requirements

- PHP 8.2+
- Laravel 11 / 12
- Livewire 3 / 4 + `livewire/flux` ^2.0 and the [apex-ui](https://github.com/jfsullivan/apex-ui) stack
- Sibling packages: `jfsullivan/member-manager`, `jfsullivan/article-manager`,
  `jfsullivan/notifications`, `jfsullivan/user-timezone`, `jfsullivan/common-helpers`

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
