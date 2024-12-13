<x-community-manager::layouts.admin>

    <x-slot name="breadcrumbs">
        <x-breadcrumbs.item url="{{ route('community.admin.index') }}"><x-apexicon-solid.home class="flex-shrink-0 h-5 w-5" /></x-breadcrumbs.item>
        <x-breadcrumbs.dividers.chevron />
        <x-breadcrumbs.item url="{{ route('community.admin.members.manage') }}">Members</x-breadcrumbs.item>
        <x-breadcrumbs.dividers.chevron />
        <x-breadcrumbs.item>Overview</x-breadcrumbs.item>
    </x-slot>

    {{-- @livewire('member-manager::livewire.member-manager', ['model_type' => 'community', 'model_id' => $community->id]) --}}

</x-community-manager::layouts.admin>
