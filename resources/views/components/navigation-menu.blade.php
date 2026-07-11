<flux:navbar class="space-x-8" aria-label="Community">
    <flux:navbar.item href="{{ route('community.dashboard') }}" :current="request()->routeIs('community.dashboard')">
        {{ __('Dashboard') }}
    </flux:navbar.item>

    <flux:navbar.item href="{{ route('community.articles.index') }}" :current="request()->routeIs('community.articles.*')">
        {{ __('News') }}
    </flux:navbar.item>

    <flux:navbar.item href="{{ route('home') }}" :current="request()->routeIs('home')">
        {{ __('Documents') }}
    </flux:navbar.item>

    <flux:navbar.item href="{{ route('home') }}" :current="request()->routeIs('home')">
        {{ __('Members') }}
    </flux:navbar.item>

    <flux:navbar.item href="{{ route('home') }}" :current="request()->routeIs('home')">
        {{ __('Calendar') }}
    </flux:navbar.item>

    <flux:navbar.item href="{{ route('community.admin.index') }}" :current="request()->routeIs('community.admin.index')">
        {{ __('Admin Tools') }}
    </flux:navbar.item>
</flux:navbar>
