<?php

namespace jfsullivan\CommunityManager\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\Notifications\Contracts\HasNotificationContext;

/** Tells the site admins that a disabled community's owner or admin asked to have it enabled again. */
class CommunityReenableRequested extends Notification implements HasNotificationContext, ShouldQueue
{
    use Queueable;

    protected string $communityName;

    protected string $requesterName;

    protected string $message;

    public function __construct(protected Community $community, Authenticatable $requester)
    {
        $this->communityName = $community->name;
        $this->requesterName = (string) ($requester->name ?? '');
        $this->message = (string) $community->reenable_request_message;
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
        return (new MailMessage)
            ->subject('Request to enable '.$this->communityName)
            ->line(trim($this->requesterName.' asked to have '.$this->communityName.' enabled again.'))
            ->line($this->message);
    }

    /**
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Request to enable '.$this->communityName,
            'body' => $this->message,
            'community' => $this->communityName,
        ];
    }

    public function notificationContext(object $notifiable): ?Model
    {
        return $this->community;
    }
}
