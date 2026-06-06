<?php
namespace App\Notifications;

use App\Models\User;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostLiked extends Notification
{
    use Queueable;

    public function __construct(
        public User $actor,
        public Post $post,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_liked',
            'actor' => [
                'id' => $this->actor->id,
                'nombre' => $this->actor->nombre,
                'avatar_url' => $this->actor->avatar && str_starts_with($this->actor->avatar, 'http') ? $this->actor->avatar : null,
            ],
            'post_id' => $this->post->id,
            'post_imagen_url' => $this->post->imagen && str_starts_with($this->post->imagen, 'http') ? $this->post->imagen : null,
        ];
    }
}
