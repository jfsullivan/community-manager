<x-apex::modal.form
    name="add-member"
    action="save"
    width="w-screen sm:max-w-md"
    heading="Add Member"
    :subheading="$show ? 'Add a member to this '.$this->owningModel()->getMorphClass().'.' : null"
    loading-target="openModal, save"
>
    @if ($show)
        <div class="w-full col-span-full grid grid-cols-6 gap-3 text-left">
            <x-apex::input.text label="First Name" wire:model="form.first_name" class="col-span-6" />

            <x-apex::input.text label="Last Name" wire:model="form.last_name" class="col-span-6" />

            <x-apex::input.email label="Email" wire:model="form.email" class="col-span-6" />

            <x-apex::input.select label="Membership Type" placeholder="Select Membership" wire:model="form.type_id" class="col-span-6">
                @foreach($this->availableMembershipTypes as $option)
                    <x-apex::input.select.option :value="$option['value']">{{ $option['label'] }}</x-apex::input.select.option>
                @endforeach
            </x-apex::input.select>

            <x-apex::input.select label="Role" placeholder="Select Type" wire:model="form.role_id" class="col-span-6">
                @foreach($this->availableRoles as $option)
                    <x-apex::input.select.option :value="$option['value']">{{ $option['label'] }}</x-apex::input.select.option>
                @endforeach
            </x-apex::input.select>

            <div class="w-full flex mt-2 col-span-6">
                <x-apex::input.checkbox wire:model="form.send_invite" label="Send an invitation email" />
            </div>
        </div>
    @endif

    <x-slot name="actions">
        <flux:button wire:click="closeModal" class="max-sm:hidden">Cancel</flux:button>
        <flux:button variant="primary" type="submit">Add Member</flux:button>
    </x-slot>
</x-apex::modal.form>
