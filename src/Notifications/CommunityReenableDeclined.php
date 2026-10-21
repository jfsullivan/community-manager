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

/** Tells a disabled community's owner and admins that their request to enable it was declined, and why. */
class CommunityReenableDeclined extends Notification implements HasNotificationContext, ShouldQueue
{
    use Queueable;

    protected string $communityName;

    protected string $message;

    protected ?string $url;

    public function __construct(protected Community $community)
    {
        $this->communityName = $community->name;
        $this->message = (string) $community->reenable_decline_message;
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
            ->subject('Your request to enable '.$this->communityName)
            ->line(config('app.name').' declined the request to enable '.$this->communityName.' again.')
            ->line($this->message);

        return $this->url ? $mail->action('Reply with a new request', $this->url) : $mail;
    }

    /**
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Request to enable '.$this->communityName.' declined',
            'body' => $this->message,
            'url' => $this->url,
            'community' => $this->communityName,
        ];
    }

    public function notificationContext(object $notifiable): ?Model
    {
        return $this->community;
    }
}
