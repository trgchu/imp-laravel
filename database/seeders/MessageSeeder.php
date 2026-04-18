<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conversations = Conversation::all();
        $users = User::all();

        if ($conversations->count() === 0 || $users->count() === 0) {
            return;
        }

        $sampleMessages = [
            'Hello everyone! 👋',
            'How are you doing today?',
            'Great to see everyone here!',
            'Has anyone tried the new feature?',
            'I think we should discuss the project plan',
            'Looking forward to working with you all',
            'Any questions about the implementation?',
            'Let\'s schedule a meeting soon',
            'Thanks for the update!',
            'This is awesome! 🎉',
            'Can someone review my code?',
            'I found a bug in the system',
            'Great discussion today!',
            'Let me know if you need help',
            'Perfect! Thanks for clarifying',
        ];

        foreach ($conversations as $conversation) {
            $conversationUsers = $conversation->users;

            if ($conversationUsers->count() === 0) {
                continue;
            }

            // Crear 10-20 mensajes por conversación
            for ($i = 0; $i < rand(10, 20); $i++) {
                $randomUser = $conversationUsers->random();
                $randomMessage = $sampleMessages[array_rand($sampleMessages)];

                Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $randomUser->id,
                    'content' => $randomMessage,
                ]);
            }
        }
    }
}
