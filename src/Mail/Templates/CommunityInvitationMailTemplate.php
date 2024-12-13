<?php

namespace jfsullivan\CommunityManager\Mail\Templates;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MailTemplates\Interfaces\MailTemplateInterface;
use Spatie\MailTemplates\Models\MailTemplate;

class CommunityInvitationMailTemplate extends MailTemplate implements MailTemplateInterface
{
    protected $table = 'mail_templates';

    public function community()
    {
        return $this->morphTo();
    }

    public function scopeForMailable(Builder $query, Mailable $mailable): Builder
    {
        return $query
            ->where('mailable', get_class($mailable))
            ->where('model_id', $mailable->getCommunityId())
            ->where('model_type', 'community');
    }

    // public function getHtmlLayout(): string
    // {
    //     return $this->meetupGroup->mail_layout;
    // }
}
