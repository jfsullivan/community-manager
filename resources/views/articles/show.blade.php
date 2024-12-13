<x-layouts.app>
    <div class="flex flex-col items-center w-full">
        <x-community-manager::page-header :community="$this->community">
            <x-slot name="navigationMenu">
                <x-community-manager::navigation-menu selected="articles" />
            </x-slot>
        </x-community-manager::page-header>

        <div class="flex justify-center w-full md:px-4">
            <div class="flex flex-col w-full max-w-5xl mt-0 sm:mt-6">
                @livewire('article-manager::livewire.show', [
                    'article_id' => $article->id,
                    'redirect' => route('community.articles.index', $community->id),
                    'model_type' => 'community',
                    'model_id' => $community->id
                ])
            </div>
        </div>
    </div>
</x-layouts.app>
