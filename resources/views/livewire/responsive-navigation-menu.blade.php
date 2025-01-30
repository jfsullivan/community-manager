<!-- Slide Over Navigation Menu -->
<x-slide-over id="navigation-menu" width="sm" class="bg-white pwa-safe-area-adjustment">
    <x-slot name="trigger">
        <button x-on:click="$dispatch('open-navigation-menu-slide-over')"
            class="inline-flex items-center justify-center p-2 transition duration-150 ease-in-out rounded-md hover:bg-gray-100/10 focus:outline-none focus:bg-transparent">
            <x-apexicon-open.menu class="w-6 h-6 text-white stroke-2" />
        </button>
    </x-slot>

    @if(Auth::check())
        @if(Auth::user()->currentCommunity)

            <div class="flex flex-col w-full border-b border-gray-200">
                <div class="flex items-center p-4">
                    <div class="flex-shrink-0">
                        @if(Auth::user()->currentCommunity->hasMedia('logo'))
                            <x-community-manager::community-logo class="w-10 h-10 mx-auto text-sm" :src="Auth::user()->currentCommunity->getFirstMediaUrl('logo')" :type="Auth::user()->currentCommunity->getFirstMedia('logo')->mime_type" />
                        @else
                            <x-profile-photo class="w-10 h-10 mx-auto text-sm" name="{{ Auth::user()->currentCommunity->name }}" />
                        @endif
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800">{{ Auth::user()->currentCommunity->name }}</div>
                        <div class="text-sm text-gray-500">{{ Auth::user()->currentCommunity->owner->name }}</div>
                    </div>
                </div>
                <div class="flex justify-between w-full px-2 pb-2">
                    @can('view-member-balance', Auth::user()->currentCommunity)
                        <div class="flex flex-col items-center justify-center">
                            <div class="flex text-xs text-gray-400">Your Balance</div>
                            @livewire('community-manager::accounting.components.member-balance')
                        </div>
                    @endcan

                    @can('add-funds', Auth::user()->currentCommunity)
                        <x-community-manager::accounting.add-funds-button size="2xs" />
                    @endcan
                </div>
            </div>

            <!-- Community Dashboard -->
            <x-community-manager::dropdown-link url="{{ route('community.dashboard') }}" show-arrow>
                <x-slot name="icon"><x-apexicon-open.home class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                {{ __('community-manager::labels.dashboard') }}
            </x-community-manager::dropdown-link>

            <!-- Community News -->
            <x-community-manager::dropdown-link url="{{ route('community.articles.index') }}" show-arrow>
                <x-slot name="icon"><x-apexicon-open.newspaper class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                {{ __('community-manager::labels.news') }}
            </x-community-manager::dropdown-link>

            <!-- Community Documents -->
            {{-- <x-community-manager::dropdown-link url="{{ route('community.articles.index') }}" show-arrow>
                <x-slot name="icon"><x-apexicon-open.users class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                {{ __('community-manager::labels.members') }}
            </x-community-manager::dropdown-link> --}}

            @can('manage', Auth::user()->currentCommunity)
                <!-- Community Admin Dashboard -->
                <x-community-manager::dropdown-link url="{{ route('community.admin.index') }}" show-arrow>
                    <x-slot name="icon"><x-apexicon-open.settings class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                    {{ __('community-manager::labels.admin-tools') }}
                </x-community-manager::dropdown-link>
            @endcan

        @endif

        @if (Auth::user()->hasCommunities())
            <x-community-manager::responsive-navigation-menu.section-header>
                {{ __('community-manager::labels.switch-community') }}
            </x-community-manager::responsive-navigation-menu.section-header>

            @foreach (Auth::user()->allCommunities() as $community)
                <x-community-manager::switchable-community :community="$community" />
            @endforeach
        @endif

        {{-- @can('create', jfsullivan\CommunityManager\Models\Community::class)
            <x-community-manager::dropdown-link url="{{ route('community.create') }}" show-arrow>
                <x-slot name="icon"><x-apexicon-open.cube class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                {{ __('community-manager::labels.create-new') }}
            </x-community-manager::dropdown-link>
        @endcan --}}

        @can('manage-site')
            <x-community-manager::responsive-navigation-menu.section-header>
                Administrator Tools
            </x-community-manager::responsive-navigation-menu.section-header>

            <div class="space-y-1 px2-2">
                <x-community-manager::dropdown-link url="{{ route('admin.dashboard') }}" show-arrow>
                    <x-slot name="icon"><x-apexicon-open.settings class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                    {{ config('app.name') }} {{ __('Admin') }}
                </x-community-manager::dropdown-link>
            </div>
        @endcan

        <x-community-manager::responsive-navigation-menu.section-header>
            Account Details
        </x-community-manager::responsive-navigation-menu.section-header>

        <div class="flex flex-col w-full">
            <div class="flex items-center p-4 border-b border-gray-200">
                <div class="flex-shrink-0">
                    <x-profile-photo class="w-10 h-10" url="{{ Auth::user()->profile_photo_url }}" name="{{ Auth::user()->name }}" />
                </div>
                <div class="ml-3">
                    <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <x-community-manager::dropdown-link url="{{ route('home') }}" show-arrow>
                <x-slot name="icon"><x-apexicon-open.home class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                {{ __('Home Page') }}
            </x-community-manager::dropdown-link>

            <x-community-manager::dropdown-link url="{{ route('profile.show') }}" show-arrow>
                <x-slot name="icon"><x-apexicon-open.user-settings class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                {{ __('Your Profile') }}
            </x-community-manager::dropdown-link>

            <!-- Authentication -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <x-community-manager::dropdown-link url="{{ route('home') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                    <x-slot name="icon"><x-apexicon-open.logout class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                    {{ __('Sign Out') }}
                </x-community-manager::dropdown-link>
            </form>
        </div>
    @endif
</x-slide-over>
