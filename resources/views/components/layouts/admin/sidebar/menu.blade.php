<!-- Sidebar menu -->
<x-apex::sidebar.nav class="py-2">
    <x-apex::sidebar.nav.item
        icon="apex-ui.speedometer"
        href="{{ route('community.admin.index') }}"
        :current="request()->routeIs('community.admin.index')"
    >
        Dashboard
    </x-apex::sidebar.nav.item>

    @if (Auth::user()->currentCommunity?->isShared())
        <x-apex::sidebar.nav.group heading="Communication">
            <x-apex::sidebar.nav.item
                icon="apex-ui.newspaper"
                href="{{ route('community.admin.articles.index') }}"
                :current="request()->routeIs('community.admin.articles.*')"
            >
                Articles
            </x-apex::sidebar.nav.item>
        </x-apex::sidebar.nav.group>

        <x-apex::sidebar.nav.group heading="Member Management">
            <x-apex::sidebar.nav.item
                icon="apex-ui.users"
                href="{{ route('community.admin.members.index') }}"
                :current="request()->routeIs('community.admin.members.index', 'community.admin.members.show')"
            >
                Members
            </x-apex::sidebar.nav.item>

            <x-apex::sidebar.nav.item
                icon="apex-ui.mail"
                href="{{ route('community.admin.members.invitations') }}"
                :current="request()->routeIs('community.admin.members.invitations')"
            >
                Invitations
            </x-apex::sidebar.nav.item>
        </x-apex::sidebar.nav.group>
    @endif

    @if (Auth::user()->currentCommunity?->isShared() && Auth::user()->currentCommunity?->track_member_balances)
        <x-apex::sidebar.nav.group heading="Accounting">
            <x-apex::sidebar.nav.item
                icon="apex-ui.coins-swap"
                href="{{ route('community.admin.accounting.transactions') }}"
                :current="request()->routeIs('community.admin.accounting.transactions')"
            >
                Transactions
            </x-apex::sidebar.nav.item>

            <x-apex::sidebar.nav.item
                icon="apex-ui.scales"
                href="{{ route('community.admin.accounting.member.balances') }}"
                :current="request()->routeIs('community.admin.accounting.member.balances')"
            >
                Member Balances
            </x-apex::sidebar.nav.item>
        </x-apex::sidebar.nav.group>
    @endif
</x-apex::sidebar.nav>
