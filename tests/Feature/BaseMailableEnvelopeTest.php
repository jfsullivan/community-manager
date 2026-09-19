<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use jfsullivan\CommunityManager\Mail\BaseMailable;
use jfsullivan\CommunityManager\Mail\Templates\CommunityInvitationMailTemplate;

class EnvelopeProbeMailable extends BaseMailable
{
    public function __construct(CommunityInvitationMailTemplate $template)
    {
        $this->mailTemplate = $template;
    }

    public function getHtmlLayout(): string
    {
        return '{{{ body }}}';
    }
}

it('keeps From on the app sending domain and routes replies to the template sender', function () {
    $template = new CommunityInvitationMailTemplate([
        'subject' => 'Hello',
        'sender_name' => 'Casey Community',
        'sender_email' => 'casey@somewhere-else.test',
        'html_template' => '<p>Hi</p>',
    ]);

    $envelope = (new EnvelopeProbeMailable($template))->envelope();

    // The template sender must never become the From address — the mail
    // provider only accepts the app's verified sending domain.
    expect($envelope->from->address)->toBe(config('mail.from.address'))
        ->and($envelope->from->name)->toBe('Casey Community')
        ->and($envelope->replyTo)->toHaveCount(1)
        ->and($envelope->replyTo[0]->address)->toBe('casey@somewhere-else.test')
        ->and($envelope->replyTo[0]->name)->toBe('Casey Community');
});

it('falls back to the app from name and no Reply-To when the template has no sender', function () {
    $template = new CommunityInvitationMailTemplate([
        'subject' => 'Hello',
        'html_template' => '<p>Hi</p>',
    ]);

    $envelope = (new EnvelopeProbeMailable($template))->envelope();

    expect($envelope->from->address)->toBe(config('mail.from.address'))
        ->and($envelope->from->name)->toBe(config('mail.from.name'))
        ->and($envelope->replyTo)->toBe([]);
});
