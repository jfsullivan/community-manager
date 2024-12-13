<div class="flex flex-col items-center w-full">
    <div class="flex justify-center w-full bg-white shadow">
        <div class="w-full px-4 sm:px-6 max-w-7xl lg:mx-auto lg:px-8">
            <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                <div class="flex-1 min-w-0">
                    <!-- Profile -->
                    <div class="flex items-center">
                        <x-profile-photo class="hidden w-16 h-16 sm:block" :url="$community->owner->profile_photo_url" :name="$community->name" />
                        <div>
                            <div class="flex items-center">
                                <x-profile-photo class="w-16 h-16 sm:hidden" :url="$community->owner->profile_photo_url" :name="$community->name" />
                                <div class="flex flex-col w-full">
                                    <h1 class="ml-3 text-2xl font-bold leading-7 text-gray-900 sm:leading-9 sm:truncate">
                                        {{ $community->name }}
                                    </h1>
                                    <dl class="flex flex-col mb-4 ml-3 space-y-1 sm:ml-3 sm:mb-0 sm:mt-1 sm:flex-row sm:flex-wrap">
                                        <dt class="sr-only">Owner</dt>
                                        <dd class="flex items-center text-sm font-medium text-gray-500 capitalize sm:mr-6">
                                            <svg class="shrink-0 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 512 512" fill="currentColor">
                                                <path d="M496 448H16c-8.84 0-16 7.16-16 16v32c0 8.84 7.16 16 16 16h480c8.84 0 16-7.16 16-16v-32c0-8.84-7.16-16-16-16zm-304-64l-64-32 64-32 32-64 32 64 64 32-64 32-16 32h208l-86.41-201.63a63.955 63.955 0 0 1-1.89-45.45L416 0 228.42 107.19a127.989 127.989 0 0 0-53.46 59.15L64 416h144l-16-32zm64-224l16-32 16 32 32 16-32 16-16 32-16-32-32-16 32-16z"/>
                                            </svg>
                                            {{ $community->owner->name }}
                                        </dd>
                                        <dt class="sr-only">Email</dt>
                                        <dd class="flex items-center text-sm font-medium text-gray-500 lowercase sm:mr-6">
                                            <svg class="shrink-0 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                            </svg>
                                            <a class="cursor-pointer hover:underline hover:text-gray-600" href="mailto:{{ $community->owner->email }}">
                                                {{ $community->owner->email }}
                                            </a>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @can('view-member-balance', $community)
                    <div class="flex text-sm text-gray-400">Account Balance</div>
                    @livewire('community-manager::livewire.accounting.member-balance', ['size' => 'lg'])
                @endcan
                
                <div class="mt-6 space-x-3 md:mt-0 md:ml-4">

                    @can('add-funds', $community)
                        <x-button theme="primary"
                            class="text-white bg-teal-600 border-teal-600 hover:bg-teal-500 active:bg-teal-700"
                            x-on:click="window.livewire.dispatch('openModal', 'accounting.payment-methods')"
                            wire:click="$dispatch('openModal', {component: 'community-manager::accounting.modals.add-funds-modal'})"
                        >
                            Add Funds
                        </x-button.primary>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->ownsCommunity($community))
        <div class="flex justify-end w-full max-w-4xl mt-4 space-x-3">
            <button type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    @if(empty($community->welcome_message))
                        Add Message
                    @else 
                        Update Message
                    @endif
            </button>
        </div>
    @endif

    @if($community->message)
        <div class="w-full max-w-4xl mt-6 bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                    Message from {{ $community->owner->name }}
                </h3>
                <div class="max-w-xl mt-2 text-sm text-gray-500">
                    <p>{!! $community->message !!}</p>
                </div>
            </div>
        </div>
    @endif

</div>