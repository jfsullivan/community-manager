<div class="flex flex-col items-center justify-center w-full px-2 mx-auto max-w-7xl md:px-4">
    <div class="flex flex-col w-full">
        <x-community-manager::navigation-menu selected="articles" />
    </div>

    <div class="w-full flex flex-col py-8">
        <div class="flex flex-col w-full">
            <div class="flex w-full text-lg font-semibold">Recent Articles</div>
        </div>

        <div
            @class([
                'w-full grid grid-cols-1 gap-4 md:gap-6',
                'lg:grid-cols-2' => $this->recentArticles->count() > 1,
            ])
        >
            @forelse($this->recentArticles as $article)
                <div
                    @class([
                        'w-full',
                        'flex sm:hidden' => $loop->count === 1,
                        'flex sm:hidden lg:flex' => ($loop->first && $loop->count > 1) || ($loop->iteration == 2 && $loop->count === 2),
                        'sm:hidden' => ($loop->iteration >= 2 && $loop->count > 2),
                        'lg:row-span-2' => $loop->first && $loop->count > 2,
                    ])
                >
                    <x-article-manager.article-card.vertical
                        class="bg-white"
                        :article="$article"
                        :url="route($this->baseRouteName.'.articles.show', collect($this->routeParameters)->merge(['article_id' => $article->id])->toArray())"
                    />
                </div>

                <div
                    @class([
                        'w-full',
                        'hidden sm:flex' => $loop->count === 1,
                        'hidden sm:flex' => $loop->count >= 1,
                        'lg:hidden' => ($loop->first && $loop->count > 1) || ($loop->iteration >= 2 && $loop->count === 2),
                    ])
                >
                    <x-article-manager.article-card.horizontal
                        class="bg-white"
                        :article="$article"
                        :url="route($this->baseRouteName.'.articles.show', collect($this->routeParameters)->merge(['article_id' => $article->id])->toArray())"
                    />
                </div>
            @empty
                <x-article-manager.empty-state />
            @endforelse
        </div>

        @if($this->olderArticles->count() > 0)
            <div class="flex flex-col w-full px-2 py-2 mt-8">
                <div class="flex w-full text-lg font-semibold sm:px-0">Older Articles</div>
            </div>
            <div
                @class([
                    'w-full grid grid-cols-1 gap-4 md:gap-6',
                    'sm:grid-cols-2 lg:grid-cols-3' => $this->olderArticles->count() > 1,
                ])
            >
                @forelse($this->olderArticles as $article)
                    <div class="flex w-full">
                        @if($loop->count === 1)
                            <div class="hidden w-full sm:flex">
                                <x-article-manager.article-card.horizontal
                                    class="bg-white"
                                    :article="$article"
                                    :url="route($this->baseRouteName.'.articles.show', collect($this->routeParameters)->merge(['article_id' => $article->id])->toArray())"
                                />
                            </div>
                            <div class="flex w-full sm:hidden">
                                <x-article-manager.article-card.vertical
                                    class="bg-white"
                                    :article="$article"
                                    :url="route($this->baseRouteName.'.articles.show', collect($this->routeParameters)->merge(['article_id' => $article->id])->toArray())"
                                />
                            </div>
                        @else
                            <x-article-manager.article-card.vertical
                                class="bg-white"
                                :article="$article"
                                :url="route($this->baseRouteName.'.articles.show', collect($this->routeParameters)->merge(['article_id' => $article->id])->toArray())"
                            />
                        @endif
                    </div>
                @empty
                    <x-article-manager.empty-state />
                @endforelse
            </div>
        @endif
    </div>
</div>
