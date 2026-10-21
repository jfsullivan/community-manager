<?php

namespace jfsullivan\CommunityManager\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\Notifications\Contracts\HasNotificationContext;

/**
 * Tells someone their request to join a community wasn't approved; no reason
 * is given. Apps swap in their own class through
 * config('community-manager.notifications.membership_rejected').
 */
class MembershipRejected extends Notification implements HasNotificationContext, ShouldQueue
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
            ->subject('Update on your request to join '.$this->communityName)
            ->line('Your request to join '.$this->communityName.' wasn\'t approved.')
            ->line('If you think this was a mistake, reach out to one of the community\'s admins.');
    }

    /**
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Request to join '.$this->communityName,
            'body' => 'Your request to join '.$this->communityName.' wasn\'t approved.',
            'community' => $this->communityName,
        ];
    }

    /** The community the request was for. */
    public function notificationContext(object $notifiable): ?Model
    {
        return $this->community;
    }
}
