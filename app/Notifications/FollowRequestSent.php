<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowRequestSent extends Notification
{
    use Queueable;

    public function __construct(
        public User $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'follow_request_sent',
            'actor' => [
                'id' => $this->actor->id,
                'nombre' => $this->actor->nombre,
                'avatar_url' => $this->actor->avatar && str_starts_with($this->actor->avatar, 'http') ? $this->actor->avatar : null,
            ],
        ];
    }
}
