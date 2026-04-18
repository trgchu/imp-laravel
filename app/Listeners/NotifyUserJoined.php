<?php

namespace App\Listeners;

use App\Events\UserJoinedConversation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyUserJoined implements ShouldQueue
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
    public function handle(UserJoinedConversation $event): void
    {
        // El broadcasting ya está manejado por ShouldBroadcast en el evento
        // Aquí puedes agregar lógica adicional como:
        // - Registrar en base de datos que el usuario se unió
        // - Enviar notificaciones a otros usuarios
        // - Actualizar estadísticas de participación
    }
} 
