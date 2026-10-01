<div class="flex flex-col items-center w-full">
    {{-- Full-width menubar bar: the border spans edge-to-edge (matching the community
         home/pools pages), while the inner container keeps the links aligned. --}}
    <div class="flex w-full bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-700">
        <div class="flex flex-col w-full px-2 mx-auto max-w-7xl md:px-4">
            <x-community-manager::navigation-menu selected="articles" />
        </div>
    </div>

    {{-- The news list itself is article-manager's card-style page, shared with pool news. --}}
    @include('article-manager::livewire.pages.article-index-page')
</div>
