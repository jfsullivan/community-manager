@php($community = $this->community)
@php($appName = config('app.name'))
<div class="flex flex-col items-center w-full">
    <div class="flex justify-center w-full py-4 bg-white border-b border-gray-200 md:py-6 dark:bg-zinc-900 dark:border-zinc-700">
        <div class="flex flex-col w-full px-4 mx-auto max-w-8xl sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                <h2 class="text-xl font-semibold text-gray-900 md:text-2xl dark:text-zinc-100">{{ $community->name }}</h2>
                <x-apex::badge.status color="red">Disabled</x-apex::badge.status>
            </div>
            <p class="text-sm text-gray-500 dark:text-zinc-400">Community</p>
        </div>
    </div>

    {{-- Shown to a disabled community's owner and admins in place of every
         page in it: why it's closed, how to ask for it back and (optional
         disabling.contact_view) how to reach the site admins. Its members
         don't see it at all. --}}
    <div class="flex flex-col w-full max-w-3xl gap-6 pb-8 sm:px-6 sm:pt-6">
        <x-apex::callout variant="danger" icon="apex-ui.lock">
            <x-apex::callout.heading>This community has been disabled</x-apex::callout.heading>
            <x-apex::callout.text>{{ $appName }} closed it. Nothing has been deleted.</x-apex::callout.text>
        </x-apex::callout>

        <x-apex::card bleed heading="Details">
            <dl class="w-full px-4 divide-y divide-zinc-100 dark:divide-zinc-800">
                <div class="grid grid-cols-3 gap-4 py-3">
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Disabled</dt>
                    <dd class="col-span-2 text-sm text-zinc-900 dark:text-zinc-100">@displayDate($community->disabled_at, 'M j, Y')</dd>
                </div>
                <div class="grid grid-cols-3 gap-4 py-3">
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Reason</dt>
                    <dd class="col-span-2 text-sm whitespace-pre-line text-zinc-900 dark:text-zinc-100">{{ filled($community->disabled_reason) ? $community->disabled_reason : 'No reason was given.' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4 py-3">
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">What it means</dt>
                    <dd class="col-span-2 text-sm text-zinc-900 dark:text-zinc-100">The community is closed until it's enabled again. Its members don't see it, and its pages and admin tools show this page instead. Nobody can join or be invited.</dd>
                </div>
            </dl>
        </x-apex::card>

        <x-apex::card bleed heading="Ask to have it enabled" :subheading="'Tell '.$appName.' what has changed, or why it should be open again.'">
            <div class="p-4">
                @if ($community->reenable_requested_at)
                    <x-apex::callout variant="info">
                        <x-apex::callout.heading>Request sent @displayDate($community->reenable_requested_at, 'M j, Y g:i A')</x-apex::callout.heading>
                        <x-apex::callout.text class="whitespace-pre-line">{{ $community->reenable_request_message }}</x-apex::callout.text>
                        <x-apex::callout.text>{{ $appName }} will review it. You can send an update below.</x-apex::callout.text>
                    </x-apex::callout>
                @elseif ($community->reenable_declined_at)
                    {{-- A declined request: the site admin's answer, which they can reply to with a new request. --}}
                    <x-apex::callout variant="warning">
                        <x-apex::callout.heading>Request declined @displayDate($community->reenable_declined_at, 'M j, Y g:i A')</x-apex::callout.heading>
                        <x-apex::callout.text class="whitespace-pre-line">{{ $community->reenable_decline_message }}</x-apex::callout.text>
                        <x-apex::callout.text>You can reply with a new request below.</x-apex::callout.text>
                    </x-apex::callout>
                @endif

                <form wire:submit="requestReenable" class="flex flex-col gap-3 {{ $community->reenable_requested_at || $community->reenable_declined_at ? 'mt-4' : '' }}">
                    <x-apex::input.textarea label="Message" wire:model="message" rows="4" placeholder="What has changed?" />
                    <div>
                        <x-apex::button type="submit" variant="primary">{{ $community->reenable_requested_at ? 'Send update' : ($community->reenable_declined_at ? 'Ask again' : 'Request re-enable') }}</x-apex::button>
                    </div>
                </form>
            </div>
        </x-apex::card>

        @if (filled(config('community-manager.disabling.contact_view')))
            @include(config('community-manager.disabling.contact_view'))
        @endif
    </div>
</div>
