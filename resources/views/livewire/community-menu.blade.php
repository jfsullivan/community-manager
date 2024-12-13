<x-dropdown dropdown-alignment="right" width-classes="w-60" bg-overlay>
    <x-slot name="trigger">
        <span class="inline-flex rounded-md">
            <div class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out border border-transparent rounded-md text-primary-50 hover:bg-white/10 hover:text-white focus:outline-none focus:bg-white/10 active:bg-white/10">

                @if(Auth::user()->currentCommunity)
                    {{ Auth::user()->currentCommunity->name }}
                @else
                    {{ __('community-manager::labels.choose') }}
                @endif

                <x-apexicon-open.chevron-selector-vertical class="ml-2 -mr-0.5 h-3.5 w-3.5 stroke-2" />

            </div>
        </span>
    </x-slot>

    <div class="flex flex-col w-full">
        @if(Auth::check())
            @if(Auth::user()->currentCommunity)
    
                <div class="flex flex-col items-center justify-center w-full px-3 pt-4 pb-0 space-y-2 border-b border-gray-200">
                    <div class="flex items-center w-full space-x-3">
                        <div class="flex items-center justify-center">
                            @if(Auth::user()->currentCommunity->hasMedia('logo'))
                                <x-community-manager::community-logo :src="Auth::user()->currentCommunity->getFirstMediaUrl('logo')" :type="Auth::user()->currentCommunity->getFirstMedia('logo')->mime_type" />
                            @else
                                <x-profile-photo class="w-8 h-8 mx-auto text-sm" name="{{ Auth::user()->currentCommunity->name }}" />
                            @endif
                        </div>
                        <div class="flex flex-col justify-center flex-grow">
                            <div class="text-sm font-semibold">{{ Auth::user()->currentCommunity->name }}</div>
                            <div class="text-xs text-gray-500">{{ Auth::user()->currentCommunity->owner->name }}</div>
                        </div>
                    </div>
                    <div class="flex justify-between w-full pb-2">
                        <div class="flex flex-col items-start justify-center text-xs">
                            <div class="flex text-xs text-gray-400">Balance</div>
                            @livewire('community-manager::accounting.components.member-balance', ['label' => 'Balance'])
                        </div>
                        <x-community-manager::accounting.add-funds-button size="2xs" />
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

                <!-- Community Admin Dashboard -->
                @can('manage', auth()->user()->currentCommunity)
                    <x-community-manager::dropdown-link url="{{ route('community.admin.index') }}" show-arrow>
                        <x-slot name="icon"><x-apexicon-open.settings class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                        {{ __('community-manager::labels.admin-tools') }}
                    </x-community-manager::dropdown-link>
                @endcan
            @endif
    
            <div class="border-t border-gray-100"></div>
    
            <!-- Community Switcher -->
            <div class="block px-4 py-2 text-xs text-gray-500">
                {{ __('community-manager::labels.switch-community') }}
            </div>
    
            @foreach (Auth::user()->allCommunities() as $community)
                <x-community-manager::switchable-community :community="$community" />
            @endforeach
    
            {{-- @can('create', jfsullivan\CommunityManager\Models\Community::class)

                <div class="border-t border-gray-200"></div>

                <x-community-manager::dropdown-link url="{{ route('community.create') }}" show-arrow>
                    <x-slot name="icon"><x-apexicon-open.cube class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                    {{ __('community-manager::labels.create-new') }}
                </x-community-manager::dropdown-link>

            @endcan --}}
        @endif
    </div>
</x-dropdown>
