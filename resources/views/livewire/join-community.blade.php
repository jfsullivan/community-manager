<x-apex::modal.form
    name="join-community"
    action="save"
    width="w-full sm:max-w-sm"
    heading="Join Community"
    subheading="Enter the ID and password provided by the community owner."
    loading-target="openModal, save"
>
    <x-slot name="icon">
        <div class="flex items-center justify-center w-12 h-12 mx-auto rounded-full shrink-0 bg-primary-100 sm:mx-0 sm:h-10 sm:w-10">
            <flux:icon name="apex-ui.community" class="w-8 h-8 sm:h-6 sm:w-6 text-primary-500 stroke-1.5"/>
        </div>
    </x-slot>

    <div class="flex flex-col w-full">
        @error('form')
            <div class="w-full pb-4 text-sm text-center text-red-500">{{ $message }}</div>
        @enderror

        <div class="flex flex-col w-full space-y-4">
            <x-apex::input.text label="Community ID" wire:model="join_id" autocorrect="off" autocapitalize="none" />
            <x-apex::input.text label="Community Password" wire:model="password" autocorrect="off" autocapitalize="none" />
        </div>
    </div>

    <x-slot name="actions">
        <flux:button wire:click="closeModal" class="max-sm:hidden">{{ __('Cancel') }}</flux:button>
        <flux:button variant="primary" type="submit">{{ __('Join') }}</flux:button>
    </x-slot>
</x-apex::modal.form>
