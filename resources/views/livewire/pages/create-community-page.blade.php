<div class="flex flex-col items-center w-full" x-data>
    <x-apex::section-header heading="Create a Community">
        <x-slot:subheading>
            <x-apex::text>Communities let you share pools, standings, and news with friends, family, or your group.</x-apex::text>
        </x-slot:subheading>
        <x-slot:actions>
            <x-apex::button size="sm" variant="ghost" icon="apex-ui.arrow-left" href="{{ route('home') }}" wire:navigate>Back to home</x-apex::button>
        </x-slot:actions>
    </x-apex::section-header>

    <div class="w-full px-4 py-8 mx-auto max-w-6xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

            {{-- Form --}}
            <div class="lg:col-span-2">
                <x-apex::form action="save">
                    @error('form')
                        <div class="pb-4 text-sm text-center text-red-500">{{ $message }}</div>
                    @enderror

                    <div class="flex flex-col w-full space-y-5">
                        <x-apex::input.text label="Community Name" wire:model="form.name" autocorrect="off" autocapitalize="none" required />

                        <x-apex::input.textarea label="Description" wire:model="form.description" rows="4" placeholder="A short description of your community…" required />

                        <x-apex::input.select label="Timezone" placeholder="Select a timezone" wire:model="form.timezone" searchable required>
                            @foreach ($this->timezoneOptions as $option)
                                <x-apex::input.select.option :value="$option['value']">{{ $option['label'] }}</x-apex::input.select.option>
                            @endforeach
                        </x-apex::input.select>

                        {{-- Premium capability --}}
                        <div class="p-4 border rounded-lg border-amber-200 bg-amber-50/60 dark:border-amber-500/40 dark:bg-amber-500/10">
                            <div class="flex items-center gap-2">
                                <flux:icon name="apex-ui.wallet" class="w-4 h-4 shrink-0 text-amber-600 stroke-1.5" />
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Member balances &amp; accounting</span>
                                <x-apex::badge size="sm" color="amber">Premium</x-apex::badge>
                            </div>
                            <p class="mt-1 text-xs text-zinc-500">Track each member's balance, entry fees, and payouts with a full transaction history. You can turn this on now or later.</p>
                            <div class="mt-3">
                                <x-apex::input.checkbox wire:model="form.track_member_balances" label="Enable member balance tracking" />
                            </div>
                        </div>
                    </div>

                    <x-slot name="actions">
                        <x-apex::button variant="primary" type="submit">{{ __('Create Community') }}</x-apex::button>
                        <x-apex::button variant="ghost" href="{{ route('home') }}" wire:navigate>{{ __('Cancel') }}</x-apex::button>
                    </x-slot>
                </x-apex::form>
            </div>

            {{-- Benefits --}}
            <aside class="lg:col-span-1">
                <div class="p-5 bg-white border rounded-xl border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">What you get with a community</h3>

                    @php
                        $benefits = [
                            ['title' => 'Share pools with your group', 'body' => 'Invite friends and family to join your pools with a simple code.'],
                            ['title' => 'Combined standings', 'body' => "See everyone on one leaderboard across all of the community's pools."],
                            ['title' => 'Community news', 'body' => 'Post announcements and updates your members see on the community home.'],
                            ['title' => 'Members & admin tools', 'body' => 'Manage members, roles, and pool settings from one admin area.'],
                        ];
                    @endphp

                    <ul class="mt-3 space-y-3">
                        @foreach ($benefits as $benefit)
                            <li class="flex gap-2.5">
                                <flux:icon name="apex-ui.check-circle" class="mt-0.5 h-4 w-4 shrink-0 text-green-500 stroke-1.5" />
                                <div>
                                    <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $benefit['title'] }}</div>
                                    <div class="text-xs text-zinc-500">{{ $benefit['body'] }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="p-3 mt-4 text-xs rounded-lg bg-zinc-50 text-zinc-500 dark:bg-zinc-900/50">
                        You always have a private personal space for your own pools — a community is for sharing with others. Premium features such as member balances &amp; accounting are part of a paid plan.
                    </div>
                </div>
            </aside>

        </div>
    </div>
</div>
