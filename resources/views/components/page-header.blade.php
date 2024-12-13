@props(['community'])

<div class="flex flex-col items-center w-full bg-white" x-data>
	<div class="flex justify-center w-full py-4 bg-white border-b border-gray-200 sm:py-6">
		<div class="flex flex-col w-full px-2 mx-auto max-w-8xl md:px-4">
            
			<div class="flex flex-col items-center space-y-6 sm:flex-row sm:justify-between">
				<div class="flex items-center space-x-5">
					<div class="flex-shrink-0">
                        @if($community->hasMedia('logo'))
                            <x-community-manager::community-logo :src="$community->getFirstMediaUrl('logo')" :type="$community->getFirstMedia('logo')->mime_type" />
                        @else
                            <x-profile-photo class="w-12 h-12 mx-auto text-2xl" name="{{ $community->name }}" />
                        @endif
					</div>
					<div class="flex flex-col items-center sm:items-start sm:pt-1">
						<div class="text-xl font-bold text-gray-900 sm:text-2xl">{{ $community->name }}</div>
                        <div class="items-center justify-center hidden text-sm font-medium text-gray-500 md:flex md:justify-start">
                            <a href="{{ route('community.dashboard') }}" class="flex items-center hover:underline">
                                <x-apexicon-open.shield-tick class="w-4 h-4 stroke-1.5 mr-1" />
                                {{ $community->owner->name }}
                            </a>
                        </div>
					</div>
				</div>

                <div class="flex items-center justify-around w-full sm:w-auto sm:space-x-6 sm:justify-center">

                    @can('view-member-balance', $community)
                        <div class="flex flex-col items-center justify-center">
                            <div class="flex text-xs text-gray-400">Your Balance</div>
                            @livewire('community-manager::accounting.components.member-balance', ['size' => 'lg'])
                        </div>
                    @endcan

                    @can('add-funds', $community)
                        <x-community-manager::accounting.add-funds-button />
                        {{-- <div class="flex justify-center mt-5 sm:mt-0">
                            <a x-on:click="window.livewire.dispatch('openModal', 'accounting.payment-methods')" 
                                class="flex items-center justify-center px-3 py-2 text-sm font-semibold text-gray-900 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                Add Funds
                            </a>
                        </div> --}}
                    @endcan
                </div>
			</div>
		</div>
	</div>
    @isset($navigationMenu)
        <div class="items-center justify-start hidden w-full bg-white shadow1 sm:flex shadow-gray-200">
            <div class="flex flex-col w-full px-2 mx-auto max-w-8xl xl:px-20">
                {{ $navigationMenu }}
            </div>
        </div>
    @endisset
</div>
