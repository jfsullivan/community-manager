<nav>
    <!-- Page Header -->
    <div 
        @class([
            'w-full px-2 mx-auto md:px-4',
            'max-w-8xl1' => !$isAdminPage,
        ])
    >
        <div class="flex justify-between h-16">
            <div class="flex">

                @if ($hasLeftMenu)
                    <!-- Flyout Left Side Menu Trigger -->
                    <div class="flex items-center md:hidden">
                        <flux:modal.trigger name="left-side-menu">
                            <span class="flex justify-center p-2 -ml-2 cursor-pointer">
                                <flux:icon name="apex-ui.menu" class="w-6 h-6 text-primary-200 stroke-2" />
                            </span>
                        </flux:modal.trigger>
                    </div>
                @endif

                <!-- Logo -->
                <div class="flex items-center w-64 px-4 -mx-4 shrink-0">
                    <a href="{{ route('index') }}">
                        <x-application-mark class="block w-auto h-12 text-xl text-white">
                            <x-application-logo />
                        </x-application-mark>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden px-4 space-x-8 sm:-my-px sm1:ml-10 sm:flex sm:items-center">
                    <x-navbar.link-pill href="{{ route('home') }}" :active="request()->routeIs('home')" active-class="text-white bg-primary-700/40" inactive-class="text-primary-200 hover:bg-white/10 hover:text-white">
                        {{ __('Home') }}
                    </x-navbar.link-pill>

                    {{-- <x-navbar.link-pill href="{{ route('home') }}" :active="!request()->routeIs('home')" active-class="text-white bg-primary-700/40" inactive-class="text-primary-200 hover:bg-white/10 hover:text-white">
                        {{ __('Communities') }}
                    </x-navbar.link-pill> --}}
                </div>
            </div>

            <div class="flex items-center justify-end sm:ml-6">

                <!-- Community Dropdown -->
                @php
                    // The community switcher is only relevant inside a community context
                    // (community or pool/entry pages). Hide it on the user home and other
                    // non-community pages so it doesn't clutter them.
                    $inCommunityContext = request()->routeIs('community.*', 'pools.*', 'entries.*');
                @endphp
                @if (Auth::user()->hasCommunities())
                    @if ($inCommunityContext)
                        <div class="relative hidden ml-3 sm:flex sm:items-center">
                            @livewire('community-manager.community-menu')
                        </div>
                    @endif
                @else
                    @can('create', jfsullivan\CommunityManager\Models\Community::class)
                        <div class="relative ml-3 mr-10">
                            <x-navbar.link href="{{ route('communities.create') }}">
                                {{ __('community-manager::labels.create-new') }}
                            </x-navbar.link>
                        </div>
                    @endcan
                @endif

                <div class="relative ml-3 text-primary-50">
                    @livewire('notifications', [
                        'triggerClasses' => 'bg-primary-600 text-primary-200 hover:bg-white/10 hover:text-white',
                        'notificationClasses' => 'bg-red-600 ring-2 ring-primary-600 text-white'
                    ])
                </div>

                <!-- Profile Menu Dropdown -->
                <x-community-manager::profile-menu />
                

                <!-- Hamburger -->
                <div class="flex items-center -mr-2 sm:hidden">
                    @livewire('community-manager.responsive-navigation-menu')
                </div>
            </div>
        </div>
    </div>

    {{-- @livewire('community-manager.responsive-navigation-menu') --}}
</nav>
