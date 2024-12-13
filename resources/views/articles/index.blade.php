<x-layouts.app>
    <div class="flex flex-col items-center w-full">
        <x-community-manager::page-header :community="$this->community">
            <x-slot name="navigationMenu">
                <x-community-manager::navigation-menu selected="articles" />
            </x-slot>
        </x-community-manager::page-header>

        <div class="flex justify-center w-full md:px-4">
            <div class="flex flex-col w-full mt-0 max-w-8xl sm:mt-6">
                @livewire('article-manager::livewire.index', [
                    'model_type' => 'community',
                    'model_id' => $community->id,
                    'route' => 'community.articles.show',
                ])
            </div>
        </div>
    </div>
</x-layouts.app>
