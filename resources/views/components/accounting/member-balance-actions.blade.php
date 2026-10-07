{{--
    Actions beside a member's balance on their own transaction history page.
    Shows Add Funds where the community allows it. Host apps can override this
    view (resources/views/vendor/community-manager/components/accounting/
    member-balance-actions.blade.php) to add their own, e.g. a payout request.
--}}
@props(['community', 'user'])

@can('add-funds', $community)
    <div class="w-full grid grid-cols-2 sm:w-auto sm:flex items-center gap-x-3">
        <x-community-manager::accounting.add-funds-button />
    </div>
@endcan
