<div class="flex w-full flex-col items-center bg-white" x-data>
	<div class="flex w-full justify-center bg-white py-6 shadow shadow-gray-200">
		<div class="max-w-8xl w-full px-4 sm:px-6 lg:mx-auto lg:px-8">
			<div class="sm:flex sm:items-center sm:justify-between">
				<div class="sm:flex sm:space-x-5">
					<div class="flex-shrink-0">
                        <x-profile-photo class="mx-auto h-20 w-20 text-2xl" url="{{ $this->user->profile_photo_url }}" name="{{ $this->user->name }}" />
					</div>
					<div class="mt-4 flex flex-col items-center sm:mt-0 sm:items-start sm:pt-1">
						<div class="text-sm font-medium text-gray-600">Welcome back,</div>
						<div class="text-xl font-bold text-gray-900 sm:text-2xl">{{ $this->user->name }}</div>
						<div class="flex items-center text-sm text-gray-600 sm:mr-6">
                            <x-apexicon-open.mail class="mr-1.5 h-5 w-5 shrink-0 text-gray-500" />
							{{ $this->user->email }}
						</div>
					</div>
				</div>
				<div class="mt-5 flex justify-center sm:mt-0">
					<a href="{{ route('profile.show') }}" class="flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        View profile
                    </a>
				</div>
			</div>
		</div>
	</div>
	<div class="mt-6 flex flex w-full justify-center bg-white">
		<div class="max-w-4xl flex w-full flex-col px-4 sm:px-6 lg:mx-auto lg:px-8">
            <x-livewire-form action="save">
                <x-slot name="title">Create Community</x-slot>
                <x-slot name="description">Create a new community to share with your friends and family.</x-slot>

                <x-slot name="content">
                    <div class="w-full flex flex-col">
                        @error('form')
                            <div class="w-full pb-4 text-sm text-red-500 text-center">{{ $message }}</div>
                        @enderror
            
                        <div class="w-full flex flex-col space-y-4">
                            <x-input.group for="name" label="Community Name" required :error="$errors->first('form.name')">
                                <x-input.text wire:model="form.name" id="name" class="max-w-xs" :error="$errors->first('form.name')" autocorrect="off" autocapitalize="none" />
                            </x-input.group>

                            <x-input.group label="Description" for="description" :error="$errors->first('form.description')" required >
                                <x-input.textarea wire:model="form.description" rows="4" placeholder="Enter a short description for you new community..."/>
                            </x-input.group>

                            <x-input.group label="Timezone" for="timezone" :error="$errors->first('form.timezone')" required >
                                <x-input.combobox 
                                    id="timezone"
                                    placeholder="Select Community Timezone"
                                    :grouped="true"
                                    wire:model.live="form.timezone"
                                    wire:search="searchTimezones"
                                    :value="$form->timezone"
                                    :error="$errors->first('form.timezone')" 
                                />
                            </x-input.group>

                            <x-input.group for="track_member_balances" :error="$errors->first('form.track_member_balances')" >
                                {{-- <div class="w-full flex mt-2 col-span-6"> --}}
                                    <x-input.checkbox name="track_member_balances" wire:model="form.track_member_balances" label="Track member balances and transactions" />
                                {{-- </div> --}}
                            </x-input.group>
                        </div>
                    </div>
                </x-slot>

                <x-slot name="actions" class="w-full md:w-auto">
                    <x-button theme="primary" class="w-full md:w-auto" type="submit">{{ __('Create Community') }}</x-button.primary>
                    <x-button theme="secondary" class="w-full md:w-auto" wire:click="$dispatch('closeModal')">{{ __('Cancel') }}</x-button.secondary>
                </x-slot>
            </x-livewire-form>
		</div>
    </div>
</div>
