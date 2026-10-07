{{--
    Actions beside a member's balance on their own transaction history page.
    Shows Add Funds where the community allows it. On phones the buttons share
    the full width equally (one or several). Host apps can override this
    view (resources/views/vendor/community-manager/components/accounting/
    member-balance-actions.blade.php) to add their own, e.g. a payout request.
--}}
@props(['community', 'user'])

@can('add-funds', $community)
    <div class="w-full grid grid-flow-col auto-cols-fr md:w-auto md:flex items-center gap-3">
        <x-community-manager::accounting.add-funds-button />
    </div>
@endcan
