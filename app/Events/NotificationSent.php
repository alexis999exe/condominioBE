<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Notification $notification
    ) {}

    /**
     * Canal privado del usuario: user.{user_id}
     * Así solo ese usuario recibe la notificación en tiempo real.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("user.{$this->notification->user_id}"),
        ];
    }

    /**
     * Nombre del evento que escucha el frontend.
     * En Laravel Reverb/Echo el "." al inicio indica evento de modelo.
     */
    public function broadcastAs(): string
    {
        return 'notification.sent';
    }

    /**
     * Datos que se envían al frontend via WebSocket.
     */
    public function broadcastWith(): array
    {
        return [
            'id'         => $this->notification->id,
            'type'       => $this->notification->type,
            'title'      => $this->notification->title,
            'message'    => $this->notification->message,
            'data'       => $this->notification->data,
            'read'       => $this->notification->read,
            'created_at' => $this->notification->created_at->toISOString(),
        ];
    }
}
