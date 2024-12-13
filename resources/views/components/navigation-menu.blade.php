<nav class="flex space-x-8" aria-label="Community">
    <x-navbar.link href="{{ route('community.dashboard') }}" :active="request()->routeIs('community.dashboard')" active-class="text-gray-700 border-primary-500" inactive-class="text-gray-500 border-transparent hover:border-gray-300 hover:text-gray-700">
        {{ __('Dashboard') }}
    </x-navbar.link>

    <x-navbar.link href="{{ route('community.articles.index') }}" :active="request()->routeIs('community.articles.*')" active-class="text-gray-700 border-primary-500" inactive-class="text-gray-500 border-transparent hover:border-gray-300 hover:text-gray-700">
        {{ __('News') }}
    </x-navbar.link>

    <x-navbar.link href="{{ route('home') }}" :active="request()->routeIs('home')" active-class="text-gray-700 border-primary-500" inactive-class="text-gray-500 border-transparent hover:border-gray-300 hover:text-gray-700">
        {{ __('Documents') }}
    </x-navbar.link>

    <x-navbar.link href="{{ route('home') }}" :active="request()->routeIs('home')" active-class="text-gray-700 border-primary-500" inactive-class="text-gray-500 border-transparent hover:border-gray-300 hover:text-gray-700">
        {{ __('Members') }}
    </x-navbar.link>

    <x-navbar.link href="{{ route('home') }}" :active="request()->routeIs('home')" active-class="text-gray-700 border-primary-500" inactive-class="text-gray-500 border-transparent hover:border-gray-300 hover:text-gray-700">
        {{ __('Calendar') }}
    </x-navbar.link>

    <x-navbar.link href="{{ route('home') }}" :active="request()->routeIs('home')" active-class="text-gray-700 border-primary-500" inactive-class="text-gray-500 border-transparent hover:border-gray-300 hover:text-gray-700">
        {{ __('Leaderboard') }}
    </x-navbar.link>

    <x-navbar.link href="{{ route('community.admin.index') }}" :active="request()->routeIs('community.admin.index')" active-class="text-gray-700 border-primary-500" inactive-class="text-gray-500 border-transparent hover:border-gray-300 hover:text-gray-700">
        {{ __('Admin Tools') }}
    </x-navbar.link>
</nav>