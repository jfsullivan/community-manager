<div class="relative hidden ml-3 sm:flex sm:items-center">
    <x-apex::dropdown position="bottom" align="end">
        <x-apex::button variant="ghost" class="rounded-full p-0!">
            <div class="flex text-sm transition duration-150 ease-in-out border-2 border-transparent rounded-full focus:outline-hidden focus:border-gray-300">
                <flux:avatar circle class="object-cover w-8 h-8" :src="Auth::user()->profile_photo_url ?: null" :name="Auth::user()->name" />
            </div>
        </x-apex::button>

        <x-apex::menu class="w-64">
            <div class="flex items-center w-full border-b border-gray-200">
                <div class="flex items-center justify-center px-3 py-4">
                    <flux:avatar circle class="object-cover w-8 h-8" :src="Auth::user()->profile_photo_url ?: null" :name="Auth::user()->name" />
                </div>
                <div class="flex flex-col justify-center grow">
                    <div class="text-sm font-semibold">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            @can('manage-site')
                <div class="block px-2 pt-2 text-xs text-gray-500">{{ __('Administration') }}</div>

                <x-community-manager::dropdown-link url="{{ route('admin.dashboard') }}" show-arrow>
                    <x-slot name="icon">
                        <flux:icon name="apex-ui.settings" class="h-5 w-5 text-gray-500 stroke-1.5" />
                    </x-slot>
                    {{ config('app.name') }} {{ __('Admin') }}
                </x-community-manager::dropdown-link>
            @endcan

            <!-- Account Management -->
            <div class="block px-2 pt-2 text-xs text-gray-500">Account Details</div>

            <x-community-manager::dropdown-link url="{{ route('home') }}" show-arrow>
                <x-slot name="icon"><flux:icon name="apex-ui.home" class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                {{ __('Home Page') }}
            </x-community-manager::dropdown-link>

            <x-community-manager::dropdown-link url="{{ route('profile.show') }}" show-arrow>
                <x-slot name="icon"><flux:icon name="apex-ui.user-settings" class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                {{ __('Your Profile') }}
            </x-community-manager::dropdown-link>

            <x-apex::menu.separator />

            <!-- Authentication -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <x-community-manager::dropdown-link url="{{ route('home') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                    <x-slot name="icon"><flux:icon name="apex-ui.logout" class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                    {{ __('Sign Out') }}
                </x-community-manager::dropdown-link>
            </form>
        </x-apex::menu>
    </x-apex::dropdown>
</div>
