<div class="flex flex-col items-center w-full bg-white dark:bg-zinc-900">
    <div class="flex justify-center w-full py-4 bg-white border-b border-gray-200 md:py-6 dark:bg-zinc-900 dark:border-zinc-700">
        <div class="flex flex-col w-full px-4 mx-auto max-w-8xl sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold text-gray-900 md:text-2xl dark:text-zinc-100">{{ $community->name }}</h2>
            @if ($community->owner)
                <p class="text-sm text-gray-500 dark:text-zinc-400">
                    Community owner: <span class="font-medium text-gray-700 dark:text-zinc-200">{{ $community->owner->name }}</span>
                </p>
            @endif
        </div>
    </div>

    <div class="flex flex-col w-full px-4 py-6 max-w-2xl sm:px-6 lg:mx-auto lg:px-8">
        <h3 class="mb-4 text-lg font-semibold text-center text-gray-900 md:text-xl dark:text-zinc-100">
            {{ $state === 'pending' ? 'Membership pending' : 'Access denied' }}
        </h3>

        {{-- The message form opens client-side; only sending needs a request. --}}
        <div class="flex flex-col p-6 border border-gray-200 rounded-lg dark:border-zinc-700" x-data="{ writing: false }" x-on:membership-message-sent.window="writing = false">
            <div class="flex items-start gap-4">
                @if ($state === 'pending')
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-full shrink-0 bg-amber-100 dark:bg-amber-500/15">
                        <flux:icon name="apex-ui.clock" class="w-6 h-6 text-amber-700 stroke-1.5 dark:text-amber-300" />
                    </span>
                @else
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-full shrink-0 bg-zinc-100 dark:bg-white/10">
                        <flux:icon name="apex-ui.info-circle" class="w-6 h-6 text-zinc-600 stroke-1.5 dark:text-zinc-300" />
                    </span>
                @endif
                <div class="flex flex-col grow">
                    @if ($state === 'pending')
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-zinc-100">Waiting for approval</h3>
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-2xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">Pending</span>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-zinc-300">
                            Your membership is awaiting approval from a community admin. You'll get
                            access to the community's pages once it's approved.
                        </p>
                        @if ($requestedAt)
                            <p class="mt-3 text-xs text-gray-500 dark:text-zinc-400">
                                Requested {{ $requestedAt->format('M j, Y') }}
                            </p>
                        @endif
                    @elseif ($state === 'rejected')
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-zinc-100">Request not approved</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-zinc-300">
                            Your request to join this community wasn't approved. If you think this was a
                            mistake, send the community's admins a message.
                        </p>
                    @else
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-zinc-100">No longer a member</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-zinc-300">
                            You're no longer a member of this community, so its pages aren't available to
                            you.
                        </p>
                    @endif
                </div>
            </div>

            @if ($sent)
                <x-apex::callout variant="success" icon="apex-ui.check-circle" class="mt-6 text-left">
                    <x-apex::callout.text>Message sent. The community's admins will reply by email.</x-apex::callout.text>
                </x-apex::callout>
            @endif

            {{-- One email to the owner and admins together, Reply-To the person, so
                 their addresses are never shown here. --}}
            <form wire:submit="sendMessage" x-show="writing" x-cloak class="flex flex-col gap-3 pt-6 mt-6 border-t border-gray-200 dark:border-zinc-700">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-zinc-100">Message the community admins</h4>
                    <p class="text-sm text-gray-500 dark:text-zinc-400">They'll reply to your email address.</p>
                </div>
                <x-apex::input.textarea wire:model="message" rows="3" aria-label="Message to the community admins"
                    placeholder="Hi, I asked to join the community…" />
                <div class="flex justify-end gap-2">
                    <x-apex::button type="button" size="sm" x-on:click="writing = false">Cancel</x-apex::button>
                    <x-apex::button type="submit" variant="primary" size="sm">Send message</x-apex::button>
                </div>
            </form>

            <div class="flex flex-col gap-3 pt-6 mt-6 border-t border-gray-200 sm:flex-row sm:justify-center dark:border-zinc-700">
                <x-apex::button variant="primary" :href="route('home')" wire:navigate>Go to my home page</x-apex::button>
                @if ($community->owner)
                    <x-apex::button x-show="! writing" x-on:click="writing = true; $nextTick(() => $el.closest('[x-data]').querySelector('textarea')?.focus())">Message the community admins</x-apex::button>
                @endif
            </div>
        </div>
    </div>
</div>
