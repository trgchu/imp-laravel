<?php

namespace App\Http\Controllers\Api;

use App\Events\ConversationUpdated;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Conversation::with(['users', 'messages'])->get();
        return response()->json(['data' => $conversations]);
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'name'        => 'required|string|max:255',
        'description' => 'nullable|string',
        'user_ids'    => 'nullable|array',
        'user_ids.*'  => 'exists:users,id',
    ]);

    $conversation = Conversation::create([
        'name'        => $validated['name'],
        'description' => $validated['description'] ?? null,
        'created_by'  => auth()->id(),
    ]);

    // Agregar al creador + usuarios adicionales
    $userIds = array_unique(array_merge(
        [$request->user()->id],
        $validated['user_ids'] ?? []
    ));
    $conversation->users()->attach($userIds);
    $conversation->load(['users', 'messages.user']);

    return response()->json(['data' => $conversation], 201);
}

    public function show(Conversation $conversation)
    {
        $conversation->load(['users', 'messages.user']);
        return response()->json(['data' => $conversation]);
    }

    public function update(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string'
        ]);

        $conversation->update($validated);

        ConversationUpdated::dispatch($conversation);

        return response()->json(['data' => $conversation]);
    }

    public function destroy(Conversation $conversation)
    {
        $conversation->delete();
        return response()->json(null, 204);
    }
}
