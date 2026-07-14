# Changelog

All notable changes to `community-manager` will be documented in this file.

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
