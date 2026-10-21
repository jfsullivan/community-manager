<?php

use Illuminate\Support\Facades\Route;
use jfsullivan\CommunityManager\Livewire\Pages\MembershipStatusPage;

/*
 * Loaded only when community-manager.membership_status.enabled is on.
 *
 * A person's own status in a specific community (pending, turned down, no
 * longer a member). Auth-only, not community-member: the member middleware
 * sends pending and turned-down requests here.
 */
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('community/{community}/membership-status', MembershipStatusPage::class)
        ->whereNumber('community')
        ->name('community.membership-status');
});
