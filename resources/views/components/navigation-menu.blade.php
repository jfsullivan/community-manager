@props([
    'selected' => null,
])

@php
    // The consuming app passes an explicit `selected` key (e.g. "dashboard",
    // "articles", "members", "admin"). Fall back to route detection so the
    // toolbar still highlights correctly when a page omits the prop.
    $selected ??= match (true) {
        request()->routeIs('community.articles.*') => 'articles',
        request()->routeIs('community.members.*') => 'members',
        request()->routeIs('community.admin.*') => 'admin',
        default => 'dashboard',
    };

    $community = Auth::user()?->currentCommunity;
@endphp

{{-- Community toolbar. Each tab pairs an apex-ui icon with its label inline,
     rendering identically at every breakpoint (the desktop look everywhere).
     Kept pool-agnostic: only community.* routes appear here. Consuming apps
     add domain tabs (e.g. Pools) via a published override.

     Mobile (< sm): each item is equal width (flex-1) and spread across the
     full row so the tabs fill the width evenly. From sm up they collapse to
     inline left-aligned tabs (w-auto) with fixed spacing. --}}
<div class="flex justify-center w-full sm:justify-start">
    <div class="flex items-end w-full sm:w-auto sm:space-x-8">
        <x-community-manager::navigation-menu.item :selected="$selected == 'dashboard'" class="flex-1 sm:flex-none"
            active-icon="home" inactive-icon="home" url="{{ route('community.dashboard') }}">
            {{ __('community-manager::labels.dashboard') }}
        </x-community-manager::navigation-menu.item>

        <x-community-manager::navigation-menu.item :selected="$selected == 'articles'" class="flex-1 sm:flex-none"
            active-icon="newspaper" inactive-icon="newspaper" url="{{ route('community.articles.index') }}">
            {{ __('community-manager::labels.news') }}
        </x-community-manager::navigation-menu.item>

        <x-community-manager::navigation-menu.item :selected="$selected == 'members'" class="flex-1 sm:flex-none"
            active-icon="users" inactive-icon="users" url="{{ route('community.members.index') }}">
            {{ __('community-manager::labels.members') }}
        </x-community-manager::navigation-menu.item>

        @if ($community && Auth::user()->can('manage', $community))
            <x-community-manager::navigation-menu.item :selected="$selected == 'admin'" class="flex-1 sm:flex-none"
                active-icon="settings" inactive-icon="settings" url="{{ route('community.admin.index') }}">
                {{ __('community-manager::labels.admin-tools') }}
            </x-community-manager::navigation-menu.item>
        @endif
    </div>
</div>
