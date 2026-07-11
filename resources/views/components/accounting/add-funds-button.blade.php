@props(['community_id' => null])

<x-apex::button variant="primary" icon="apex-ui.credit-card-upload"
    {{ $attributes }}
    wire:click="$dispatch('open-add-funds', { community_id: {{ $community_id ?? 'null' }} })"
>
    Add Funds
</x-apex::button>
