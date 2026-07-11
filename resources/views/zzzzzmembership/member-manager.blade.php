<x-community-manager::layouts.admin>

    <x-slot name="breadcrumbs">
        <x-apex::breadcrumbs.item href="{{ route('community.admin.index') }}"><flux:icon name="apex-ui.home" class="shrink-0 h-5 w-5" /></x-apex::breadcrumbs.item>
        <x-apex::breadcrumbs.item href="{{ route('community.admin.members.manage') }}">Members</x-apex::breadcrumbs.item>
        <x-apex::breadcrumbs.item>Overview</x-apex::breadcrumbs.item>
    </x-slot>

    {{-- @livewire('member-manager::livewire.member-manager', ['model_type' => 'community', 'model_id' => $community->id]) --}}

</x-community-manager::layouts.admin>
