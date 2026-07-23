{{-- Breadcrumb items for a community member's details page (the admin layout supplies the wrapper + dashboard icon). --}}
<x-apex::breadcrumbs.item>Member Management</x-apex::breadcrumbs.item>
<x-apex::breadcrumbs.item :href="route('community.admin.members.index')">Members</x-apex::breadcrumbs.item>
<x-apex::breadcrumbs.item>{{ $this->member->full_name }}</x-apex::breadcrumbs.item>
