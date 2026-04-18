<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine whether the user can view any messages.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the message.
     */
    public function view(User $user, Message $message): bool
    {
        return $user->conversations->contains($message->conversation_id);
    }

    /**
     * Determine whether the user can create messages.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the message.
     */
    public function update(User $user, Message $message): bool
    {
        return $user->id === $message->user_id && 
               $user->conversations->contains($message->conversation_id);
    }

    /**
     * Determine whether the user can delete the message.
     */
    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->user_id && 
               $user->conversations->contains($message->conversation_id);
    }

    /**
     * Determine whether the user can restore the message.
     */
    public function restore(User $user, Message $message): bool
    {
        return $user->id === $message->user_id;
    }

    /**
     * Determine whether the user can permanently delete the message.
     */
    public function forceDelete(User $user, Message $message): bool
    {
        return $user->id === $message->user_id;
    }
}
