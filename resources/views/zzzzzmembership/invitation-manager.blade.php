<x-brain-layouts.organization-admin>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs.item url="{{ route('organizations.admin.index') }}"><x-apexicon-solid.home class="flex-shrink-0 h-5 w-5" /></x-breadcrumbs.item>
        <x-breadcrumbs.dividers.chevron />
        <x-breadcrumbs.item url="{{ route('organizations.admin.members.manage') }}">Members</x-breadcrumbs.item>
        <x-breadcrumbs.dividers.chevron />
        <x-breadcrumbs.item>Invitations</x-breadcrumbs.item>
    </x-slot>


    @livewire('member-manager::livewire.invitation-manager', ['model' => $organization])

</x-brain-layouts.organization-admin>
