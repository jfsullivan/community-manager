<!-- Side bar component that displays as off-canvas for smaller screens. -->
<x-sidebar trigger-teleport="#header-left-panel" id="admin-sidebar" :attributes="$attributes">
    <x-slot name="header">
        <div class="flex flex-col w-full">
            <div class="flex items-center justify-center w-full px-4 py-3 border-b xl:justify-start xl:space-x-2 border-primary-700/50">
                <x-apexicon-open.settings class="h-5 w-5 text-white stroke-1.5" />
                <div class="hidden text-white font-base xl:inline whitespace-nowrap">Community Administration</div>
            </div>

            <div class="hidden w-full px-2 py-3 space-y-1 border-b xl:flex xl:flex-col border-primary-700/50">
                <div class="px-2 uppercase text-primary-200 text-2xs">Current Community</div>

                <x-dropdown dropdown-alignment="right" width-classes="w-60" class="w-full">
                    <x-slot name="trigger" class="w-full">
                        <span class="inline-flex w-full rounded-md">
                            <div
                                class="inline-flex items-center justify-between w-full px-3 py-2 text-sm font-medium leading-4 text-white transition duration-150 ease-in-out border border-transparent rounded-md bg-gray-900/10 focus:outline-none focus:bg-gray-900/20 active:bg-gray-900/20">

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
                            @foreach (Auth::user()->allCommunities() as $community)
                                <x-community-manager::switchable-community :community="$community" />
                            @endforeach
                        @endif
                    </div>
                </x-dropdown>
            </div>

        </div>
    </x-slot>

    {{-- <x-slot name="offCanvas" class="bg-gray-900" trigger-class="md:hidden">
        <x-layouts.admin-side-bar.menu />
    </x-slot> --}}

    <x-slot name="staticSidebar" class="bg-primary-600">
        <x-community-manager::layouts.admin.sidebar.menu />
    </x-slot>
</x-sidebar>
