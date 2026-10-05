<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminSystemNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly string $level = 'info',
        private readonly ?string $actionUrl = null,
        private readonly ?string $actionLabel = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'level' => in_array($this->level, ['info', 'success', 'warning', 'danger'], true)
                ? $this->level
                : 'info',
            'action_url' => $this->actionUrl,
            'action_label' => $this->actionLabel,
        ];
    }
}
