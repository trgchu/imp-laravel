<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Conversation;
use Symfony\Component\HttpFoundation\Response;

class BroadcastAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar autenticación
        if (!auth('sanctum')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Extraer el conversation_id del canal
        if ($request->has('channel_name')) {
            $channelName = $request->input('channel_name');
            
            // Formato esperado: conversation.{id}
            if (preg_match('/^conversation\.(\d+)$/', $channelName, $matches)) {
                $conversationId = $matches[1];
                $conversation = Conversation::find($conversationId);

                if (!$conversation) {
                    return response()->json(['error' => 'Conversation not found'], 404);
                }

                // Verificar que el usuario pertenece a la conversación
                if (!auth()->user()->conversations->contains($conversation)) {
                    return response()->json(['error' => 'Forbidden'], 403);
                }
            }
        }

        return $next($request);
    }
}
