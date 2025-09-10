<x-slide-over.form action="save" class="text-white bg-gray-700">

    <x-slot name="leftHeader"><x-button.link x-on:click="$wire.closeModal()">Cancel</x-button.link></x-slot>
    <x-slot name="title"><span class="text-xs sm:text-sm">{{ (empty($this->form->id)) ? 'Create' : 'Edit' }} Transaction</span></x-slot>
    <x-slot name="rightHeader"><x-button.link type="submit">Save</x-button.link></x-slot> 

    <x-slot name="message">
        @if(empty($this->form->id)) 
            Add a transaction for one of you community members.
        @else
            Update the transaction for one of your community members.
        @endif
    </x-slot>

    <x-slot name="content">
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

            <x-input.group for="transacted_at" label="Date" required class="col-span-6" :error="$errors->first('form.transacted_at')">
                <x-input.date.flatpickr wire:model="form.transacted_at" id="transacted_at" />
            </x-input.group>

            <x-input.group for="type_id" label="Type" required class="col-span-6" :error="$errors->first('form.type_id')">
                <x-input.select.custom 
                    id="type_id"
                    placeholder="Select Type"
                    x-model="$wire.form.type_id"
                    :options="$this->transactionTypes"
                    :error="$errors->first('form.type_id')"
                >
                    <x-slot name="selectedIcon">
                        <x-apexicon-open.check class="w-5 h-5 stroke-2" />
                    </x-slot>
                </x-input.select.custom>

            </x-input.group>

            {{-- <x-brain-transaction-custom-inputs :data="$custom_input_data" /> --}}

            {{-- <div class="text-xs" x-text="JSON.stringify($wire.form)"></div> --}}
            {{-- @if(!empty($transferDirection)) --}}
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
            {{-- @endif --}}

            {{-- <x-brain-transaction-custom-inputs /> --}}

            <x-input.group for="description" label="Description" class="col-span-6" :error="$errors->first('form.description')">
                <x-input.text wire:model="form.description" id="description" />
            </x-input.group>
            <x-input.group for="amount" label="Amount" required class="col-span-2" :error="$errors->first('form.amount')">
                <x-input.money wire:model="form.amount" id="amount" />
            </x-input.group>
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-button theme="primary" type="submit">Save</x-button.primary>
        <x-button theme="secondary" x-on:click="$wire.closeModal()">Cancel</x-button.secondary>
    </x-slot>
</x-slide-over.form>
