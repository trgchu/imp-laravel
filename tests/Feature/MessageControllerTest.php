<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->conversation = Conversation::factory()->create([
            'created_by' => $this->user->id,
        ]);
        $this->conversation->users()->attach($this->user->id);
    }

    /**
     * Test getting all messages for a conversation.
     */
    public function test_get_all_messages(): void
    {
        Message::factory(5)->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/conversations/{$this->conversation->id}/messages"
        );

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /**
     * Test creating a message.
     */
    public function test_create_message(): void
    {
        $data = [
            'content' => 'This is a test message',
        ];

        $response = $this->actingAs($this->user)->postJson(
            "/api/conversations/{$this->conversation->id}/messages",
            $data
        );

        $response->assertStatus(201);
        $response->assertJsonPath('data.content', 'This is a test message');
        $response->assertJsonPath('data.user_id', $this->user->id);

        $this->assertDatabaseHas('messages', [
            'content' => 'This is a test message',
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test getting a single message.
     */
    public function test_get_single_message(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/conversations/{$this->conversation->id}/messages/{$message->id}"
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $message->id);
        $response->assertJsonPath('data.content', $message->content);
    }

    /**
     * Test updating a message.
     */
    public function test_update_message(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user->id,
        ]);

        $data = [
            'content' => 'Updated message content',
        ];

        $response = $this->actingAs($this->user)->putJson(
            "/api/conversations/{$this->conversation->id}/messages/{$message->id}",
            $data
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.content', 'Updated message content');

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'content' => 'Updated message content',
        ]);
    }

    /**
     * Test deleting a message.
     */
    public function test_delete_message(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(
            "/api/conversations/{$this->conversation->id}/messages/{$message->id}"
        );

        $response->assertStatus(200);

        $this->assertDatabaseMissing('messages', [
            'id' => $message->id,
        ]);
    }

    /**
     * Test user cannot delete another user's message.
     */
    public function test_user_cannot_delete_others_message(): void
    {
        $otherUser = User::factory()->create();
        $this->conversation->users()->attach($otherUser->id);

        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(
            "/api/conversations/{$this->conversation->id}/messages/{$message->id}"
        );

        $response->assertStatus(403);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
        ]);
    }

    /**
     * Test pagination of messages.
     */
    public function test_messages_pagination(): void
    {
        Message::factory(25)->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/conversations/{$this->conversation->id}/messages?per_page=10"
        );

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta.total', 25);
    }
}
