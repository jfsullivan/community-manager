<x-brain-layouts.organization-admin>
    <x-slot name="breadcrumbs">
        <x-apex::breadcrumbs.item href="{{ route('organizations.admin.index') }}"><flux:icon name="apex-ui.home" class="shrink-0 h-5 w-5" /></x-apex::breadcrumbs.item>
        <x-apex::breadcrumbs.item href="{{ route('organizations.admin.members.manage') }}">Members</x-apex::breadcrumbs.item>
        <x-apex::breadcrumbs.item>Invitations</x-apex::breadcrumbs.item>
    </x-slot>


    @livewire('member-manager::livewire.invitation-manager', ['model' => $organization])

</x-brain-layouts.organization-admin>
