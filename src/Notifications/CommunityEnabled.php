<?php

namespace jfsullivan\CommunityManager\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\Notifications\Contracts\HasNotificationContext;

/** Tells a community's owner and admins that it has been enabled again. */
class CommunityEnabled extends Notification implements HasNotificationContext, ShouldQueue
{
    use Queueable;

    protected string $communityName;

    public function __construct(protected Community $community)
    {
        $this->communityName = $community->name;
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
            ->subject($this->communityName.' is open again')
            ->line(config('app.name').' has enabled '.$this->communityName.' again. Everything is back as it was.');
    }

    /**
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => $this->communityName.' is open again',
            'body' => config('app.name').' has enabled it again.',
            'community' => $this->communityName,
        ];
    }

    public function notificationContext(object $notifiable): ?Model
    {
        return $this->community;
    }
}
