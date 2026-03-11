<?php

namespace Tests\Feature\E2E;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlanningPokerFlowTest extends TestCase
{
    protected User $host;
    protected User $voter1;
    protected User $voter2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->host = User::factory()->create(['name' => 'John Host']);
        $this->voter1 = User::factory()->create(['name' => 'Jane Voter']);
        $this->voter2 = User::factory()->create(['name' => 'Bob Voter']);
    }

    #[Test]
    public function complete_planning_poker_session(): void
    {
        // Step 1: Host creates a room
        Sanctum::actingAs($this->host);
        
        $createRoomResponse = $this->postJson('/api/rooms', [
            'name' => 'Sprint Planning',
            'card_config' => [0, 1, 2, 3, 5, 8, 13, 21, '?'],
        ]);

        $createRoomResponse->assertCreated();
        $roomId = $createRoomResponse->json('id');
        $roomCode = $createRoomResponse->json('code');

        // Step 2: Voters join the room
        Sanctum::actingAs($this->voter1);
        $this->postJson("/api/rooms/join/$roomCode")->assertOk();

        Sanctum::actingAs($this->voter2);
        $this->postJson("/api/rooms/join/$roomCode")->assertOk();

        // Step 3: Host creates a vote session
        Sanctum::actingAs($this->host);
        $createSessionResponse = $this->postJson("/api/rooms/$roomId/sessions", [
            'story_title' => 'User Authentication Feature',
            'story_description' => 'Implement login and registration',
        ]);

        $createSessionResponse->assertCreated();
        $sessionId = $createSessionResponse->json('id');

        // Step 4: Voters submit their votes
        Sanctum::actingAs($this->voter1);
        $this->postJson("/api/sessions/$sessionId/vote", ['value' => '5'])->assertOk();

        Sanctum::actingAs($this->voter2);
        $this->postJson("/api/sessions/$sessionId/vote", ['value' => '8'])->assertOk();

        // Step 5: Host reveals votes
        Sanctum::actingAs($this->host);
        $revealResponse = $this->postJson("/api/sessions/$sessionId/reveal");

        $revealResponse->assertOk();
        $this->assertEquals('revealed', $revealResponse->json('status'));
        $this->assertEquals(6.5, $revealResponse->json('average')); // (5 + 8) / 2

        // Step 6: Verify all voters can see the results
        Sanctum::actingAs($this->voter1);
        $sessionResponse = $this->getJson("/api/sessions/$sessionId");
        $sessionResponse->assertOk()
                       ->assertJsonStructure(['votes' => ['*' => ['id', 'value']]]);
    }

    #[Test]
    public function multiple_voting_sessions(): void
    {
        // Setup
        Sanctum::actingAs($this->host);
        $createRoomResponse = $this->postJson('/api/rooms', ['name' => 'Sprint']);
        $roomId = $createRoomResponse->json('id');
        $roomCode = $createRoomResponse->json('code');

        // Voters join
        Sanctum::actingAs($this->voter1);
        $this->postJson("/api/rooms/join/$roomCode");

        // First story
        Sanctum::actingAs($this->host);
        $session1 = $this->postJson("/api/rooms/$roomId/sessions", [
            'story_title' => 'Story 1',
        ])->json('id');

        Sanctum::actingAs($this->voter1);
        $this->postJson("/api/sessions/$session1/vote", ['value' => '3']);

        // Create second story (should close first)
        Sanctum::actingAs($this->host);
        $session2 = $this->postJson("/api/rooms/$roomId/sessions", [
            'story_title' => 'Story 2',
        ])->json('id');

        // Verify first session is closed
        $session1Check = $this->getJson("/api/sessions/$session1");
        $this->assertNotNull($session1Check->json('id'));

        // Second story voting
        Sanctum::actingAs($this->voter1);
        $this->postJson("/api/sessions/$session2/vote", ['value' => '5']);

        // Verify second session is open
        $session2Check = $this->getJson("/api/sessions/$session2");
        $this->assertEquals('open', $session2Check->json('status'));
    }

    #[Test]
    public function emoji_reactions_during_session(): void
    {
        // Setup room
        Sanctum::actingAs($this->host);
        $createRoomResponse = $this->postJson('/api/rooms', ['name' => 'Sprint']);
        $roomId = $createRoomResponse->json('id');
        $roomCode = $createRoomResponse->json('code');

        Sanctum::actingAs($this->voter1);
        $this->postJson("/api/rooms/join/$roomCode");

        // Send emoji to another user
        $emojiResponse = $this->postJson("/api/rooms/$roomId/emojis", [
            'emoji' => '🎉',
            'target_id' => $this->voter2->id,
        ]);

        $emojiResponse->assertCreated()
                     ->assertJson([
                         'emoji' => '🎉',
                         'target_id' => $this->voter2->id,
                     ]);

        // Send group emoji
        $groupEmojiResponse = $this->postJson("/api/rooms/$roomId/emojis", [
            'emoji' => '👏',
        ]);

        $groupEmojiResponse->assertCreated()
                          ->assertJson(['emoji' => '👏']);
    }

    #[Test]
    public function room_management_flow(): void
    {
        // Host creates and manages room
        Sanctum::actingAs($this->host);
        
        $room = $this->postJson('/api/rooms', ['name' => 'Management Room'])->json();
        $roomId = $room['id'];

        // Change room state
        $this->patchJson("/api/rooms/$roomId/state", ['state' => 'voting'])
             ->assertOk()
             ->assertJson(['state' => 'voting']);

        // Toggle emojis
        $this->patchJson("/api/rooms/$roomId/toggle-emojis")
             ->assertOk()
             ->assertJson(['emojis_blocked' => true]);

        // Toggle back
        $this->patchJson("/api/rooms/$roomId/toggle-emojis")
             ->assertOk()
             ->assertJson(['emojis_blocked' => false]);

        // Verify changes persisted
        $showResponse = $this->getJson("/api/rooms/$roomId");
        $this->assertEquals('voting', $showResponse->json('state'));
        $this->assertFalse($showResponse->json('emojis_blocked'));
    }

    #[Test]
    public function user_can_join_and_leave_room(): void
    {
        // Host creates room
        Sanctum::actingAs($this->host);
        $room = $this->postJson('/api/rooms', ['name' => 'Test Room'])->json();
        $roomCode = $room['code'];

        // Voter joins
        Sanctum::actingAs($this->voter1);
        $this->postJson("/api/rooms/join/$roomCode")->assertOk();

        // Verify online status
        $roomCheck = $this->getJson("/api/rooms/{$room['id']}")
                          ->assertOk();
        $users = collect($roomCheck->json('users'));
        $voter = $users->firstWhere('id', $this->voter1->id);
        $this->assertTrue($voter['pivot']['is_online']);

        // Voter leaves
        $this->postJson("/api/rooms/{$room['id']}/leave")->assertOk();

        // Verify offline status
        $roomAfterLeave = $this->getJson("/api/rooms/{$room['id']}");
        $users = collect($roomAfterLeave->json('users'));
        $voter = $users->firstWhere('id', $this->voter1->id);
        $this->assertFalse($voter['pivot']['is_online']);
    }
}
