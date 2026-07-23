<!-- Community administration sidebar (apex-ui) -->
<x-apex::sidebar id="admin-sidebar" class="h-full">
    <x-apex::sidebar.header>
        <div class="flex flex-col w-full">
            {{-- Full labels on mobile (flyout) and xl; icon-only in the md-lg rail. --}}
            <div class="flex items-center justify-start space-x-2 w-full px-4 py-3 border-b md:justify-center md:space-x-0 xl:justify-start xl:space-x-2 border-zinc-200 dark:border-zinc-700">
                <flux:icon name="apex-ui.settings" class="h-5 w-5 text-zinc-500 dark:text-zinc-400 stroke-1.5" />
                <div class="md:max-xl:hidden font-medium text-zinc-700 dark:text-zinc-200 whitespace-nowrap">Community Administration</div>
            </div>

            <div class="md:max-xl:hidden flex flex-col w-full px-2 py-3 space-y-1 border-b border-zinc-200 dark:border-zinc-700">
                <div class="px-2 uppercase text-zinc-500 text-2xs">Current Community</div>

                <x-apex::dropdown position="bottom" align="end" class="w-full">
                    <x-apex::button variant="ghost" class="w-full justify-between px-3 py-2 text-sm font-medium leading-4 text-zinc-700! bg-zinc-100 hover:bg-zinc-200 active:bg-zinc-200 dark:text-zinc-200! dark:bg-zinc-800">
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
                                @foreach (Auth::user()->allCommunities() as $community)
                                    <x-community-manager::switchable-community :community="$community" />
                                @endforeach
                            @endif
                        </div>
                    </x-apex::menu>
                </x-apex::dropdown>
            </div>

        </div>
    </x-apex::sidebar.header>

    <x-community-manager::layouts.admin.sidebar.menu />
</x-apex::sidebar>
