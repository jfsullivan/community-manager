<x-apex::drawer.form
    :name="$modalName"
    action="save"
    width="w-screen sm:max-w-xl"
    :heading="(empty($this->form->id)) ? 'Create Transaction' : 'Edit Transaction'"
    :subheading="(empty($this->form->id)) ? 'Add a transaction for one of your community members.' : 'Update the transaction for one of your community members.'"
    loading-target="openModal, save"
>
    <div
        class="grid w-full grid-cols-6 gap-3 text-left col-span-full"
        x-data="{
            transactionTypes: @js($this->transactionTypes),
            isTranferTransaction() {
                transaction = this.transactionTypes.find(type => type.value == $wire.form.type_id);
                if(!transaction) {
                    return false;
                }

                return transaction.slug == 'transfer-in' || transaction.slug == 'transfer-out';
            }
        }"
    >
        <x-apex::input.select label="User" placeholder="Select User" wire:model="form.user_id" searchable wire:search="userSearchTerm" required class="col-span-6">
            @foreach ($this->userOptions as $option)
                <x-apex::input.select.option :value="$option['value']">{{ $option['label'] }}</x-apex::input.select.option>
            @endforeach
        </x-apex::input.select>

        <x-apex::input.date-picker label="Date" wire:model="form.transacted_at" class="col-span-6" />

        <x-apex::input.select label="Type" placeholder="Select Type" wire:model="form.type_id" class="col-span-6">
            @foreach($this->transactionTypes as $option)
                <x-apex::input.select.option :value="$option['value']">{{ $option['label'] }}</x-apex::input.select.option>
            @endforeach
        </x-apex::input.select>

        <div x-show="isTranferTransaction" class="flex w-full col-span-6">
            <x-apex::input.select label="Transfer" placeholder="Select User" wire:model="form.transfer_user_id" searchable wire:search="transferUserSearchTerm" required class="w-full">
                @foreach ($this->transferUserOptions as $option)
                    <x-apex::input.select.option :value="$option['value']">{{ $option['label'] }}</x-apex::input.select.option>
                @endforeach
            </x-apex::input.select>
        </div>

        <x-apex::input.text label="Description" wire:model="form.description" class="col-span-6" />

        <x-apex::input.money label="Amount" wire:model="form.amount" class="col-span-2" />
    </div>

    <x-slot name="actions">
        <flux:button wire:click="closeModal" class="max-sm:hidden">Cancel</flux:button>
        <flux:button variant="primary" type="submit">Save</flux:button>
    </x-slot>
</x-apex::drawer.form>
