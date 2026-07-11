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
        <x-input.group for="user_id" label="User" required class="col-span-6" :error="$errors->first('form.user_id')">
            <x-input.combobox
                id="user"
                placeholder="Select User"
                wire:model="form.user_id"
                wire:search="searchUsers"
                :searchTerm="$this->userSearchTerm"
                :error="$errors->first('form.user_id')"
            >{{ $this->userSearchTerm ?? '' }}</x-input.combobox>
        </x-input.group>

        <x-apex::input.date-picker label="Date" wire:model="form.transacted_at" class="col-span-6" />

        <x-apex::input.select label="Type" placeholder="Select Type" wire:model="form.type_id" class="col-span-6">
            @foreach($this->transactionTypes as $option)
                <x-apex::input.select.option :value="$option['value']">{{ $option['label'] }}</x-apex::input.select.option>
            @endforeach
        </x-apex::input.select>

        <div x-show="isTranferTransaction" class="flex w-full col-span-6">
            <x-input.group for="transfer_user_id" label="Transfer" required :error="$errors->first('form.transfer_user_id')">
                <x-input.combobox
                    id="transfer_user_id"
                    placeholder="Select User"
                    wire:model="form.transfer_user_id"
                    wire:search="searchTransferUsers"
                    :searchTerm="$this->transferUserSearchTerm"
                    :error="$errors->first('form.transfer_user_id')"
                />
            </x-input.group>
        </div>

        <x-apex::input.text label="Description" wire:model="form.description" class="col-span-6" />

        <x-apex::input.money label="Amount" wire:model="form.amount" class="col-span-2" />
    </div>

    <x-slot name="actions">
        <flux:button wire:click="closeModal" class="max-sm:hidden">Cancel</flux:button>
        <flux:button variant="primary" type="submit">Save</flux:button>
    </x-slot>
</x-apex::drawer.form>
