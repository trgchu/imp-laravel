<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendMessageNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        // Obtener todos los usuarios en la conversación excepto el que envió el mensaje
        $users = $event->message->conversation->users()
            ->where('user_id', '!=', $event->message->user_id)
            ->get();

        // Aquí puedes enviar notificaciones (email, SMS, push notifications, etc.)
        // Por ahora, el broadcasting ya está manejado por ShouldBroadcast en el evento
    }
}
