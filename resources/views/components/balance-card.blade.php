@props(['community'])

{{--
    The member's balance in a community, shown as a card. Rendered below the menubar
    by the page-header so it's visible on every community page (not just the home),
    and self-guards on track_member_balances so it never shows for personal communities.
--}}
@if ($community->track_member_balances)
    @php $memberBalanceCents = (int) $community->memberBalance(auth()->user()); @endphp

    <div {{ $attributes->class('flex flex-col items-start w-full gap-3 p-4 bg-white border rounded-lg border-gray-200 sm:flex-row sm:items-center sm:justify-between') }}>
        <div class="flex items-center gap-3">
            <span class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 shrink-0">
                <flux:icon name="apex-ui.scales" class="w-5 h-5 text-primary-600 stroke-1.5" />
            </span>
            <div>
                <div class="text-2xs uppercase tracking-wide text-gray-400">Your balance in {{ $community->name }}</div>
                <div @class([
                    'text-xl font-semibold',
                    'text-green-600' => $memberBalanceCents > 0,
                    'text-red-600' => $memberBalanceCents < 0,
                    'text-gray-900' => $memberBalanceCents === 0,
                ])>
                    {{ $memberBalanceCents < 0 ? '-' : '' }}${{ number_format(abs($memberBalanceCents) / 100, 2) }}
                </div>
            </div>
        </div>

        @can('add-funds', $community)
            <div class="flex items-center gap-2 shrink-0">
                <x-community-manager::accounting.add-funds-button size="sm" />
            </div>
        @endcan
    </div>
@endif
