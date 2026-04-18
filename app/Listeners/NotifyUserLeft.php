<?php

namespace App\Listeners;

use App\Events\UserLeftConversation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyUserLeft implements ShouldQueue
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
    public function handle(UserLeftConversation $event): void
    {
        // El broadcasting ya está manejado por ShouldBroadcast en el evento
        // Aquí puedes agregar lógica adicional como:
        // - Registrar en base de datos que el usuario se fue
        // - Actualizar el estado de la conversación
        // - Notificar a otros usuarios
    }
}
