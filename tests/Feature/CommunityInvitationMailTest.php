<?php

use jfsullivan\CommunityManager\Mail\CommunityInvitation;

it('includes the sanitized personal note, the inviter and the community description', function () {
    createRoute('register', 'register');
    $userClass = config('community-manager.user_model');
    $owner = $userClass::factory()->create(['name' => 'Olive Owner']);
    $community = createCommunity($owner);
    $community->update(['description' => 'Tuesday nine holes at Lakeside.']);

    $invitation = $community->invitations()->create([
        'email' => 'guest@example.test',
        'message' => '<p>Bring <em>clubs</em></p><img src=x onerror=alert(1)>',
        'last_sent_by' => $owner->id,
        'last_sent_at' => now(),
    ]);

    $html = (new CommunityInvitation($community, $invitation))->getHtmlLayout();

    expect($html)
        ->toContain('Olive Owner wrote:')
        ->toContain('<em>clubs</em>')
        ->not->toContain('onerror')
        ->toContain('Tuesday nine holes at Lakeside.');
});
