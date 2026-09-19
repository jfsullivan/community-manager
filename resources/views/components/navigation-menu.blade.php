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

{{-- Community toolbar. Each tab pairs an apex-ui icon with its label, mirroring
     the app's pool dashboard toolbar (icon stacked above the label on mobile,
     beside it on md+). Kept pool-agnostic: only community.* routes appear here.
     Consuming apps add domain tabs (e.g. Pools) via a published override.

     Mobile (< sm): the tabs spread evenly across the full row (justify-between)
     so 3-5 items fill the width, matching the pool toolbar. From sm up they
     collapse to inline left-aligned tabs with fixed spacing. --}}
<div class="flex justify-center w-full sm:justify-start">
    <div class="flex items-end justify-between w-full sm:w-auto sm:justify-start sm:space-x-8">
        <x-community-manager::navigation-menu.item :selected="$selected == 'dashboard'"
            active-icon="home" inactive-icon="home" url="{{ route('community.dashboard') }}">
            {{ __('community-manager::labels.dashboard') }}
        </x-community-manager::navigation-menu.item>

        <x-community-manager::navigation-menu.item :selected="$selected == 'articles'"
            active-icon="newspaper" inactive-icon="newspaper" url="{{ route('community.articles.index') }}">
            {{ __('community-manager::labels.news') }}
        </x-community-manager::navigation-menu.item>

        <x-community-manager::navigation-menu.item :selected="$selected == 'members'"
            active-icon="users" inactive-icon="users" url="{{ route('community.members.index') }}">
            {{ __('community-manager::labels.members') }}
        </x-community-manager::navigation-menu.item>

        @if ($community && Auth::user()->can('manage', $community))
            <x-community-manager::navigation-menu.item :selected="$selected == 'admin'"
                active-icon="settings" inactive-icon="settings" url="{{ route('community.admin.index') }}">
                {{ __('community-manager::labels.admin-tools') }}
            </x-community-manager::navigation-menu.item>
        @endif
    </div>
</div>
