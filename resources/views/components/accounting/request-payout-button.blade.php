@props(['community_id' => null])

<x-apex::button icon="apex-ui.credit-card-download"
    {{ $attributes }}
    wire:click="$dispatch('openModal', { component: 'community-manager.accounting.request-payout-modal', props: { community_id: {{ $community_id ?? 'null' }} } })"
>
    Request Payout
</x-apex::button>
