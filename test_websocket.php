<?php
require 'vendor/autoload.php';

use Pusher\Pusher;

$pusher = new Pusher(
    'test_key',
    'test_secret',
    '123456',
    ['cluster' => 'mt1']
);

// Simular un mensaje
$message = [
    'id' => 45,
    'content' => 'Test message via WebSocket',
    'user' => ['id' => 16, 'name' => 'Test User'],
    'conversation_id' => 4,
    'created_at' => now()->toIso8601String(),
];

$pusher->trigger('conversation.4', 'MessageSent', $message);

echo "✓ Evento enviado exitosamente\n";
