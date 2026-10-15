<x-apex::modal.form
    name="join-community"
    action="save"
    width="w-full sm:max-w-sm"
    heading="Join Community"
    subheading="Enter the ID and password provided by the community owner."
    loading-target="openModal, save"
>
    <x-slot:icon>
        <x-apex::featured-icon name="apex-ui.community" />
    </x-slot:icon>

    <div class="flex flex-col w-full">
        @error('form')
            <div class="w-full pb-4 text-sm text-center text-red-500 dark:text-red-400">{{ $message }}</div>
        @enderror

        <div class="flex flex-col w-full space-y-4">
            <x-apex::input.text label="Community ID" wire:model="join_id" autocorrect="off" autocapitalize="none" />
            <x-apex::input.text label="Community Password" wire:model="password" autocorrect="off" autocapitalize="none" />
        </div>
    </div>

    <x-slot name="actions">
        <flux:modal.close>
            <x-apex::button>{{ __('Cancel') }}</x-apex::button>
        </flux:modal.close>
        <x-apex::button variant="primary" type="submit">{{ __('Join') }}</x-apex::button>
    </x-slot>
</x-apex::modal.form>
