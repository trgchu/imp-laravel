<?php

namespace App\Listeners;

use App\Events\ConversationUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateConversationUsers implements ShouldQueue
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
    public function handle(ConversationUpdated $event): void
    {
        // El broadcasting ya está manejado por ShouldBroadcast en el evento
        // Aquí puedes agregar lógica adicional como:
        // - Actualizar caché
        // - Registrar cambios en logs
        // - Enviar notificaciones a usuarios
    }
}
