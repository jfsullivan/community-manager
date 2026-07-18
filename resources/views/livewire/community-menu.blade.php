<x-apex::dropdown position="bottom" align="end">
    <x-apex::button variant="ghost" class="px-3 py-2 text-sm font-medium leading-4 text-primary-50! hover:bg-white/10 hover:text-white!">
        @if(Auth::user()->currentCommunity)
            {{ Auth::user()->currentCommunity->name }}
        @else
            {{ __('community-manager::labels.choose') }}
        @endif

        <flux:icon name="apex-ui.chevron-selector-vertical" class="ml-2 -mr-0.5 h-3.5 w-3.5 stroke-2" />
    </x-apex::button>

    <x-apex::menu class="w-60">
        <div class="flex flex-col w-full">
            @if(Auth::check())
                @if(Auth::user()->currentCommunity)

                    <div class="flex flex-col items-center justify-center w-full px-3 pt-4 pb-0 space-y-2 border-b border-gray-200">
                        <div class="flex items-center w-full space-x-3">
                            <div class="flex items-center justify-center">
                                @if(Auth::user()->currentCommunity->hasMedia('logo'))
                                    <x-community-manager::community-logo :src="Auth::user()->currentCommunity->getFirstMediaUrl('logo')" :type="Auth::user()->currentCommunity->getFirstMedia('logo')->mime_type" />
                                @else
                                    <flux:avatar circle class="w-8 h-8 mx-auto text-sm" :name="Auth::user()->currentCommunity->name" />
                                @endif
                            </div>
                            <div class="flex flex-col justify-center grow">
                                <div class="text-sm font-semibold">{{ Auth::user()->currentCommunity->name }}</div>
                                <div class="text-xs text-gray-500">{{ Auth::user()->currentCommunity->owner->name }}</div>
                            </div>
                        </div>
                        @if (Auth::user()->currentCommunity->track_member_balances)
                            <div class="flex justify-between w-full pb-2">
                                <div class="flex flex-col items-start justify-center text-xs">
                                    <div class="flex text-xs text-gray-400">Balance</div>
                                    @livewire('community-manager.accounting.components.member-balance', ['label' => 'Balance'])
                                </div>
                                <x-community-manager::accounting.add-funds-button size="xs" />
                            </div>
                        @endif
                    </div>

                    <!-- Community Dashboard -->
                    <x-community-manager::dropdown-link url="{{ route('community.dashboard') }}" show-arrow>
                        <x-slot name="icon"><flux:icon name="apex-ui.home" class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                        {{ __('community-manager::labels.dashboard') }}
                    </x-community-manager::dropdown-link>

                    <!-- Community News -->
                    @if (Auth::user()->currentCommunity->isShared())
                        <x-community-manager::dropdown-link url="{{ route('community.articles.index') }}" show-arrow>
                            <x-slot name="icon"><flux:icon name="apex-ui.newspaper" class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                            {{ __('community-manager::labels.news') }}
                        </x-community-manager::dropdown-link>
                    @endif

                    <!-- Community Documents -->
                    {{-- <x-community-manager::dropdown-link url="{{ route('community.articles.index') }}" show-arrow>
                        <x-slot name="icon"><flux:icon name="apex-ui.users" class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                        {{ __('community-manager::labels.members') }}
                    </x-community-manager::dropdown-link> --}}

                    <!-- Community Admin Dashboard -->
                    @can('manage', auth()->user()->currentCommunity)
                        <x-community-manager::dropdown-link url="{{ route('community.admin.index') }}" show-arrow>
                            <x-slot name="icon"><flux:icon name="apex-ui.settings" class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                            {{ __('community-manager::labels.admin-tools') }}
                        </x-community-manager::dropdown-link>
                    @endcan

                    {{-- Quick-jump to your pools in this community (the community menubar isn't
                         shown on pool pages, so this is the fast path between pools). --}}
                    @php
                        $switcherPools = Auth::user()->currentCommunity
                            ->pools()
                            ->whereRelation('members', 'user_id', Auth::user()->id)
                            ->orderByDesc('start_at')
                            ->limit(6)
                            ->get(['id', 'name']);
                    @endphp
                    @if ($switcherPools->isNotEmpty())
                        <x-apex::menu.separator />
                        <x-apex::menu.heading>{{ __('Your pools') }}</x-apex::menu.heading>
                        @foreach ($switcherPools as $switcherPool)
                            <x-community-manager::dropdown-link url="{{ route('pools.home', ['pool_id' => $switcherPool->id]) }}">
                                <x-slot name="icon"><flux:icon name="apex-ui.trophy" class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                                <span class="truncate">{{ $switcherPool->name }}</span>
                            </x-community-manager::dropdown-link>
                        @endforeach
                    @endif
                @endif

                <x-apex::menu.separator />

                <!-- Community Switcher -->
                <x-apex::menu.heading>
                    {{ __('community-manager::labels.switch-community') }}
                </x-apex::menu.heading>

                @foreach (Auth::user()->allCommunities() as $community)
                    <x-community-manager::switchable-community :community="$community" />
                @endforeach

                {{-- @can('create', jfsullivan\CommunityManager\Models\Community::class)

                    <x-apex::menu.separator />

                    <x-community-manager::dropdown-link url="{{ route('community.create') }}" show-arrow>
                        <x-slot name="icon"><flux:icon name="apex-ui.cube" class="h-5 w-5 text-gray-500 stroke-1.5" /></x-slot>
                        {{ __('community-manager::labels.create-new') }}
                    </x-community-manager::dropdown-link>

                @endcan --}}
            @endif
        </div>
    </x-apex::menu>
</x-apex::dropdown>
