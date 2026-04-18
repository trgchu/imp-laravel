<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test getting all conversations.
     */
    public function test_get_all_conversations(): void
    {
        Conversation::factory(3)->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/conversations');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /**
     * Test getting a single conversation.
     */
    public function test_get_single_conversation(): void
    {
        $conversation = Conversation::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/conversations/{$conversation->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $conversation->id);
        $response->assertJsonPath('data.name', $conversation->name);
    }

    /**
     * Test creating a conversation.
     */
    public function test_create_conversation(): void
    {
        $data = [
            'name' => 'New Conversation',
            'description' => 'A new test conversation',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/conversations', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'New Conversation');
        $response->assertJsonPath('data.description', 'A new test conversation');
        $response->assertJsonPath('data.created_by', $this->user->id);

        $this->assertDatabaseHas('conversations', array_merge($data, ['created_by' => $this->user->id]));
    }

    /**
     * Test updating a conversation.
     */
    public function test_update_conversation(): void
    {
        $conversation = Conversation::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $data = [
            'name' => 'Updated Conversation',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->user)->putJson("/api/conversations/{$conversation->id}", $data);

        $response->assertStatus(200);
        $response->assertJsonPath('data.name', 'Updated Conversation');

        $this->assertDatabaseHas('conversations', $data);
    }

    /**
     * Test deleting a conversation.
     */
    public function test_delete_conversation(): void
    {
        $conversation = Conversation::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/conversations/{$conversation->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('conversations', [
            'id' => $conversation->id,
        ]);
    }

    /**
     * Test adding users to a conversation.
     */
    public function test_add_users_to_conversation(): void
    {
        $conversation = Conversation::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $users = User::factory(2)->create();

        $data = [
            'user_ids' => $users->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($this->user)->postJson(
            "/api/conversations/{$conversation->id}/users",
            $data
        );

        $response->assertStatus(200);

        foreach ($users as $user) {
            $this->assertTrue($conversation->users()->where('user_id', $user->id)->exists());
        }
    }

    /**
     * Test removing users from a conversation.
     */
    public function test_remove_users_from_conversation(): void
    {
        $conversation = Conversation::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $users = User::factory(2)->create();
        $conversation->users()->attach($users->pluck('id')->toArray());

        $data = [
            'user_ids' => [$users->first()->id],
        ];

        $response = $this->actingAs($this->user)->deleteJson(
            "/api/conversations/{$conversation->id}/users",
            $data
        );

        $response->assertStatus(200);

        $this->assertFalse(
            $conversation->users()->where('user_id', $users->first()->id)->exists()
        );
    }
}
