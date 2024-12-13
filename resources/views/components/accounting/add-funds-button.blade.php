@props(['community_id' => null])

<x-button theme="success" 
    {{ $attributes }}
    wire:click="$dispatch('openModal', { component: 'community-manager::accounting.modals.add-funds-modal', props: { community_id: {{ $community_id ?? 'null' }} } })"
>
    <x-slot name="leadingIcon">
        <x-apexicon-open.credit-card-upload class="h-full w-full stroke-2" />
    </x-slot>

    Add Funds
</x-button.success>