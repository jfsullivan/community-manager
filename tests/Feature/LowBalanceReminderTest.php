<?php

use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;

beforeEach(function () {
    config()->set('community-manager.features.track_member_balances', true);

    $userClass = config('community-manager.user_model');
    $this->member = $userClass::factory()->create();
    $this->community = Community::factory()->create(['track_member_balances' => true, 'low_balance_threshold' => 1000]);
    addCommunityMember($this->community, $this->member);
    $this->member->save();

    Transaction::factory()->create([
        'community_id' => $this->community->id,
        'user_id' => $this->member->id,
        'type_id' => TransactionType::where('slug', 'deposit')->value('id'),
        'amount' => 4, // dollars
    ]);
});

it('reminds a member below the threshold once a week', function () {
    actingAs($this->member);

    expect((string) $this->blade('<x-community-manager::low-balance-reminder />'))->toContain('Time to add funds')
        ->and((string) $this->blade('<x-community-manager::low-balance-reminder />'))->not->toContain('Time to add funds');
});

it('stays quiet without a threshold or balance tracking', function () {
    actingAs($this->member);
    $this->community->update(['low_balance_threshold' => null]);

    expect((string) $this->blade('<x-community-manager::low-balance-reminder />'))->not->toContain('Time to add funds');
});
