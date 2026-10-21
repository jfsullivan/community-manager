<?php

namespace jfsullivan\CommunityManager\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\Notifications\Contracts\HasNotificationContext;

/**
 * Tells a community's owner and admins that a site admin disabled it, and
 * why, with a link to the page where they can ask for it back.
 */
class CommunityDisabled extends Notification implements HasNotificationContext, ShouldQueue
{
    use Queueable;

    protected string $communityName;

    protected string $reason;

    protected ?string $url;

    public function __construct(protected Community $community)
    {
        $this->communityName = $community->name;
        $this->reason = (string) $community->disabled_reason;
        $this->url = Route::has('community.disabled') ? route('community.disabled', ['community_id' => $community->id]) : null;
    }

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->communityName.' has been disabled')
            ->line(config('app.name').' has disabled '.$this->communityName.'. Its members can\'t open it until it\'s enabled again; nothing has been deleted.')
            ->line('Reason: '.($this->reason !== '' ? $this->reason : 'No reason was given.'));

        return $this->url ? $mail->action('Ask to have it enabled', $this->url) : $mail;
    }

    /**
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => $this->communityName.' has been disabled',
            'body' => $this->reason !== '' ? $this->reason : 'No reason was given.',
            'url' => $this->url,
            'community' => $this->communityName,
        ];
    }

    public function notificationContext(object $notifiable): ?Model
    {
        return $this->community;
    }
}
