<!-- Sidebar menu -->
<x-sidebar.menu class="py-2">
    <x-sidebar.menu.group>
        <x-sidebar.menu.item.primary 
            label="Dashboard"
            icon="apexicon-open.speedometer"
            url="{{ route('community.admin.index') }}" 
            :active="request()->routeIs('community.admin.index')"
        />
    </x-sidebar.menu.group>

    <x-sidebar.menu.group>
        <x-sidebar.menu.section-header.primary>Communication</x-sidebar.menu.section-header.primary>

        <x-sidebar.menu.item.primary 
            label="Articles" 
            icon="apexicon-open.newspaper"
            url="{{ route('community.admin.articles.index') }}"
            :active="request()->routeIs('community.admin.articles.*')"
        />

    </x-sidebar.menu.group>

    <x-sidebar.menu.group>
        <x-sidebar.menu.section-header.primary>Member Management</x-sidebar.menu.section-header.primary>

        <x-sidebar.menu.item.primary
            label="Members" 
            icon="apexicon-open.users"
            url="{{ route('community.admin.members.index') }}" 
            :active="request()->routeIs('community.admin.member.*')"
        />
    </x-sidebar.menu.group>

    <x-sidebar.menu.group>
        <x-sidebar.menu.section-header.primary>Accounting</x-sidebar.menu.section-header.primary>
    
        <x-sidebar.menu.item.primary
            label="Transactions" 
            icon="apexicon-open.coins-swap"
            url="{{ route('community.admin.accounting.transactions') }}" 
            :active="request()->routeIs('community.admin.accounting.transactions')"
        />

        <x-sidebar.menu.item.primary
            label="Member Balances" 
            icon="apexicon-open.scales"
            url="{{ route('community.admin.accounting.member.balances') }}"
            :active="request()->routeIs('community.admin.accounting.member.balances')"
        />
    </x-sidebar.menu.group>
</x-sidebar.menu>
