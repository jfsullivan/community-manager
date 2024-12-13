<x-modal.livewire action="addMember">
    <x-slot name="title">Add Member</x-slot>
    <x-slot name="description">Add a member to this {{ $this->owningModel()->getMorphClass() }}.</x-slot>
    <x-slot name="content">
        <div class="w-full col-span-full grid grid-cols-6 gap-3 text-left">
            <x-input.group for="first_name" label="First Name" required class="col-span-6" :error="$errors->first('form.first_name')">
                <x-input.text wire:model="form.first_name" id="first_name" :error="$errors->first('form.first_name')" />
            </x-input.group>

            <x-input.group for="last_name" label="Last Name" required class="col-span-6" :error="$errors->first('form.last_name')">
                <x-input.text wire:model="form.last_name" id="last_name" :error="$errors->first('form.last_name')" />
            </x-input.group>

            <x-input.group for="email" label="Email" required class="col-span-6" :error="$errors->first('form.email')">
                <x-input.email wire:model="form.email" id="email" :error="$errors->first('form.email')" />
            </x-input.group>

            <x-input.group for="type_id" label="Membership Type" required class="col-span-6" :error="$errors->first('form.type_id')">
                <x-input.select.custom
                    id="type_id"
                    placeholder="Select Membership"
                    wire:model="form.type_id"
                    :options="$this->availableMembershipTypes"
                    :error="$errors->first('form.type_id')"
                >
                    <x-slot name="selectedIcon">
                        <x-apexicon-open.check class="w-5 h-5 stroke-2" />
                    </x-slot>
                </x-input.select.custom>
            </x-input.group>

            <x-input.group for="role_id" label="Role" required class="col-span-6" :error="$errors->first('form.role_id')">
                <x-input.select.custom
                    id="role_id"
                    placeholder="Select Type"
                    wire:model="form.role_id"
                    :options="$this->availableRoles"
                    :error="$errors->first('form.role_id')"
                >
                    <x-slot name="selectedIcon">
                        <x-apexicon-open.check class="w-5 h-5 stroke-2" />
                    </x-slot>
                </x-input.select.custom>
            </x-input.group>

            <div class="w-full flex mt-2 col-span-6">
                <x-input.checkbox name="send_invite" wire:model="form.send_invite" label="Send an invitation email" />
            </div>
        </div>
    </x-slot>
    <x-slot name="actions">
        <x-button theme="primary" type="submit">Add Member</x-button.primary>
        <x-button theme="secondary" wire:click="$dispatch('closeModal')">Cancel</x-button.secondary>
    </x-slot>
</x-modal.livewire>
