<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $conversationId = $request->query('conversation_id');

        $query = Message::query();

        if ($conversationId) {
            $query->where('conversation_id', $conversationId);
        }

        return $query->with('user', 'conversation')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'conversation_id' => 'required|exists:conversations,id',
        ]);

        $conversation = Conversation::findOrFail($validated['conversation_id']);

        // Verificar que el usuario pertenece a esta conversación
        if (!auth()->user()->conversations->contains($conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message = auth()->user()->messages()->create($validated);
        $message->load('user', 'conversation');

        MessageSent::dispatch($message);

        return response()->json($message, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        return $message->load('user', 'conversation');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message)
    {
        $this->authorize('update', $message);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message->update($validated);

        return response()->json($message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        $this->authorize('delete', $message);

        $message->delete();

        return response()->json(null, 204);
    }
}
