<?php

use Illuminate\Support\Facades\Route;
use jfsullivan\CommunityManager\Livewire\Pages\DisabledCommunityPage;
use jfsullivan\CommunityManager\Livewire\Pages\UnavailablePage;

/*
 * Loaded only when community-manager.disabling.enabled is on.
 */
Route::middleware(['web', 'auth'])->group(function () {
    // A disabled community's explanation page for its owner and admins (why,
    // and a way to ask for it back). It finds the community by id, not the
    // current-community context, and sends everyone else on.
    Route::get('community/{community_id}/disabled', DisabledCommunityPage::class)
        ->whereNumber('community_id')
        ->name('community.disabled');

    // Where members land when they open something disabled: a short "isn't
    // available" note with no reason. Named outside community.* so the
    // disabled-community lock never applies to it.
    Route::get('unavailable/{kind}', UnavailablePage::class)
        ->whereIn('kind', config('community-manager.disabling.unavailable_kinds', ['community']))
        ->name('unavailable');
});
