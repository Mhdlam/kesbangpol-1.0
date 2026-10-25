<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WebPushGenericNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $actionUrl = null,
        public ?string $type = null
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(mixed $notifiable, mixed $notification): WebPushMessage
    {
        $url = $this->actionUrl ?: url('/');

        // Build clean tag for Windows grouping/deduplication
        $tag = 'kesbangpol-' . ($this->type ?: 'general') . '-' . substr(md5($this->title . $url), 0, 8);

        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->icon(url('/images/notification-icon.png'))
            ->badge(url('/images/notification-badge.png'))
            ->tag($tag)
            ->requireInteraction(true)
            ->renotify(true)
            ->vibrate([200, 100, 200])
            ->data([
                'url' => $url,
                'type' => $this->type,
                'timestamp' => now()->timestamp,
            ]);
    }
}
