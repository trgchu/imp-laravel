<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $conversations = auth()->user()->conversations()
            ->with('users', 'messages')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json($conversations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $conversation = Conversation::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Agregar el creador a la conversación
        $userIds = $validated['user_ids'];
        if (!in_array(auth()->id(), $userIds)) {
            $userIds[] = auth()->id();
        }

        $conversation->users()->sync($userIds);
        $conversation->load('users', 'messages');

        return response()->json($conversation, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversation $conversation)
    {
        // Verificar que el usuario pertenece a esta conversación
        if (!auth()->user()->conversations->contains($conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($conversation->load('users', 'messages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Conversation $conversation)
    {
        // Solo el creador puede actualizar
        if (!auth()->user()->conversations->contains($conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $conversation->update($validated);

        return response()->json($conversation->load('users', 'messages'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conversation $conversation)
    {
        // Solo el creador puede eliminar
        if (!auth()->user()->conversations->contains($conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->delete();

        return response()->json(null, 204);
    }

    /**
     * Add user to conversation
     */
    public function addUser(Request $request, Conversation $conversation)
    {
        if (!auth()->user()->conversations->contains($conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $conversation->users()->attach($validated['user_id']);

        return response()->json($conversation->load('users'));
    }

    /**
     * Remove user from conversation
     */
    public function removeUser(Request $request, Conversation $conversation)
    {
        if (!auth()->user()->conversations->contains($conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $conversation->users()->detach($validated['user_id']);

        return response()->json($conversation->load('users'));
    }
}
