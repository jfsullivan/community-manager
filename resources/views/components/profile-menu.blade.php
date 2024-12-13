<div class="relative hidden ml-3 sm:flex sm:items-center">
    <x-dropdown dropdown-alignment="right" width-classes="w-64" bg-overlay>
        <x-slot name="trigger">
            <div class="flex text-sm transition duration-150 ease-in-out border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300">
                <x-profile-photo class="object-cover w-8 h-8" url="{{ Auth::user()->profile_photo_url }}" name="{{ Auth::user()->name }}" />
            </div>
        </x-slot>

        <div class="flex items-center w-full border-b border-gray-200">
            <div class="flex items-center justify-center px-3 py-4">
                <x-profile-photo class="object-cover w-8 h-8" url="{{ Auth::user()->profile_photo_url }}" name="{{ Auth::user()->name }}" />
            </div>
            <div class="flex flex-col justify-center flex-grow">
                <div class="text-sm font-semibold">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
            </div>
        </div>

        @can('manage-site')
            <x-dropdown.section-header class="px-4 pt-2">{{ __('Administration') }}</x-dropdown.section-header>
            
            <x-community-manager::dropdown-link url="{{ route('admin.dashboard') }}" show-arrow>
                <x-slot name="icon">
                    <x-apexicon-open.settings class="h-5 w-5 text-gray-500 stroke-1.5" />
                </x-slot>
                {{ config('app.name') }} {{ __('Admin') }}
            </x-community-manager::dropdown-link>
        @endcan

        <!-- Account Management -->
        <x-dropdown.section-header class="px-4 pt-2">Account Details</x-dropdown.section-header>

        <x-community-manager::dropdown-link url="{{ route('home') }}" show-arrow>
            <x-slot name="icon"><x-apexicon-open.home class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
            {{ __('Home Page') }}
        </x-community-manager::dropdown-link>

        <x-community-manager::dropdown-link url="{{ route('profile.show') }}" show-arrow>
            <x-slot name="icon"><x-apexicon-open.user-settings class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
            {{ __('Your Profile') }}
        </x-community-manager::dropdown-link>

        <x-dropdown.divider class="bg-gray-200" />

        <!-- Authentication -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <x-community-manager::dropdown-link url="{{ route('home') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                <x-slot name="icon"><x-apexicon-open.logout class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                {{ __('Sign Out') }}
            </x-community-manager::dropdown-link>
        </form>
    </x-dropdown>
</div>