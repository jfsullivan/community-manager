@props(['community'])

<div class="flex flex-col items-center w-full bg-white" x-data>
	<div class="flex justify-center w-full py-4 bg-white border-b border-gray-200 sm:py-6">
		<div class="flex flex-col w-full px-2 mx-auto max-w-8xl md:px-4">
            
			<div class="flex flex-col items-center space-y-6 sm:flex-row sm:justify-between">
				<div class="flex items-center space-x-5">
					<div class="shrink-0">
                        @if($community->hasMedia('logo'))
                            <x-community-manager::community-logo :src="$community->getFirstMediaUrl('logo')" :type="$community->getFirstMedia('logo')->mime_type" />
                        @else
                            <flux:avatar circle class="w-12 h-12 mx-auto text-2xl" name="{{ $community->name }}" />
                        @endif
					</div>
					<div class="flex flex-col items-center sm:items-start sm:pt-1">
						<div class="text-xl font-bold text-gray-900 sm:text-2xl">{{ $community->name }}</div>
                        <div class="items-center justify-center hidden text-sm font-medium text-gray-500 md:flex md:justify-start">
                            <a href="{{ route('community.dashboard') }}" class="flex items-center hover:underline">
                                <flux:icon name="apex-ui.shield-tick" class="w-4 h-4 stroke-1.5 mr-1" />
                                {{ $community->owner->name }}
                            </a>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>
    @isset($navigationMenu)
        {{-- Full-width bottom border under the menubar (spans edge-to-edge because this
             wrapper is w-full; the inner container just constrains the links). --}}
        <div class="items-center justify-start hidden w-full bg-white border-b border-gray-200 sm:flex">
            <div class="flex flex-col w-full px-2 mx-auto max-w-8xl xl:px-20">
                {{ $navigationMenu }}
            </div>
        </div>
    @endisset
</div>
