@props(['community_id' => null])

<x-button.outline color="gray"
    {{ $attributes }}
    wire:click="$dispatch('openModal', { component: 'community-manager::accounting.request-payout-modal', props: { community_id: {{ $community_id ?? 'null' }} } })"
>
    <x-slot name="leadingIcon">
        <x-apexicon-open.credit-card-download class="h-full w-full stroke-2" />
    </x-slot>

    Request Payout
</x-button.outline>