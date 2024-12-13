<x-modal action="join">
    
    <x-slot name="icon">
        <div class="flex items-center justify-center w-12 h-12 mx-auto rounded-full shrink-0 bg-primary-100 sm:mx-0 sm:h-10 sm:w-10">
            <x-apexicon-open.community class="w-8 h-8 sm:h-6 sm:w-6 text-primary-500 stroke-1.5"/>
        </div>
    </x-slot>
    
    <x-slot name="title">Join Community</x-slot>
    <x-slot name="message">Enter the ID and password provided by the community owner.</x-slot>

    <x-slot name="content">
        <div class="flex flex-col w-full">
            @error('form')
                <div class="w-full pb-4 text-sm text-center text-red-500">{{ $message }}</div>
            @enderror

            <div class="flex flex-col w-full space-y-4">
                <x-input.group for="join_id" label="Community ID" required :error="$errors->first('join_id')">
                    <x-input.text wire:model="join_id" id="join_id" :error="$errors->first('join_id')" autocorrect="off" autocapitalize="none" />
                </x-input.group>
                <x-input.group label="Community Password" for="password" :required="true" class="" :error="$errors->first('password')">
                    <x-input.text wire:model="password" id="password" :error="$errors->first('password')" autocorrect="off" autocapitalize="none" />
                </x-input.group>
            </div>
        </div>
    </x-slot>

    <x-slot name="actions" class="w-full">
        <x-button theme="primary" class="w-full sm:flex-1 sm:w-auto" type="submit">{{ __('Join') }}</x-button.primary>
        <x-button theme="secondary" class="w-full sm:flex-1 sm:w-auto" wire:click="$dispatch('closeModal')">{{ __('Cancel') }}</x-button.secondary>
    </x-slot>
</x-modal>
