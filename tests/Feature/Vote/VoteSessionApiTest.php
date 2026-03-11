<?php

namespace Tests\Feature\Vote;

use App\Models\Room;
use App\Models\User;
use App\Models\VoteSession;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VoteSessionApiTest extends TestCase
{
    protected User $user;
    protected User $otherUser;
    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        
        $this->room = Room::factory()->create(['host_id' => $this->user->id]);
        $this->room->users()->attach($this->user->id, ['role' => 'host']);
        $this->room->users()->attach($this->otherUser->id, ['role' => 'voter']);

        Sanctum::actingAs($this->user);
    }

    #[Test]
    public function store_creates_vote_session(): void
    {
        // Arrange
        $data = [
            'story_title' => 'User authentication',
            'story_description' => 'Implement login flow',
        ];

        // Act
        $response = $this->postJson("/api/rooms/{$this->room->id}/sessions", $data);

        // Assert
        $response->assertCreated()
                 ->assertJsonStructure([
                     'id',
                     'room_id',
                     'story_title',
                     'status',
                 ]);

        $this->assertEquals('User authentication', $response->json('story_title'));
        $this->assertDatabaseHas('vote_sessions', [
            'room_id' => $this->room->id,
            'story_title' => 'User authentication',
        ]);
    }

    #[Test]
    public function store_requires_host_authorization(): void
    {
        // Arrange
        Sanctum::actingAs($this->otherUser);

        $data = [
            'story_title' => 'Test',
        ];

        // Act
        $response = $this->postJson("/api/rooms/{$this->room->id}/sessions", $data);

        // Assert
        $response->assertForbidden();
    }

    #[Test]
    public function submit_vote_stores_vote(): void
    {
        // Arrange
        $session = VoteSession::factory()->create(['room_id' => $this->room->id]);
        Sanctum::actingAs($this->otherUser);

        // Act
        $response = $this->postJson("/api/sessions/$session->id/vote", [
            'value' => '5',
        ]);

        // Assert
        $response->assertOk()
                 ->assertJson(['message' => 'Vote submitted.']);

        $this->assertDatabaseHas('votes', [
            'vote_session_id' => $session->id,
            'user_id' => $this->otherUser->id,
            'value' => '5',
        ]);
    }

    #[Test]
    public function submit_vote_fails_if_user_not_member(): void
    {
        // Arrange
        $other = User::factory()->create();
        $session = VoteSession::factory()->create(['room_id' => $this->room->id]);
        Sanctum::actingAs($other);

        // Act
        $response = $this->postJson("/api/sessions/$session->id/vote", [
            'value' => '5',
        ]);

        // Assert
        $response->assertForbidden();
    }

    #[Test]
    public function reveal_shows_all_votes(): void
    {
        // Arrange
        $session = VoteSession::factory()->open()->create(['room_id' => $this->room->id]);
        
        \App\Models\Vote::factory()->create([
            'vote_session_id' => $session->id,
            'user_id' => $this->user->id,
            'value' => '3',
        ]);

        \App\Models\Vote::factory()->create([
            'vote_session_id' => $session->id,
            'user_id' => $this->otherUser->id,
            'value' => '5',
        ]);

        // Act
        $response = $this->postJson("/api/sessions/$session->id/reveal");

        // Assert
        $response->assertOk()
                 ->assertJsonStructure([
                     'id',
                     'status',
                     'average',
                     'revealed_at',
                     'votes' => [
                         '*' => ['id', 'value', 'user'],
                     ],
                 ]);

        $this->assertEquals('revealed', $response->json('status'));
        $this->assertEquals(4, $response->json('average'));
    }

    #[Test]
    public function reveal_requires_host_authorization(): void
    {
        // Arrange
        $session = VoteSession::factory()->create(['room_id' => $this->room->id]);
        Sanctum::actingAs($this->otherUser);

        // Act
        $response = $this->postJson("/api/sessions/$session->id/reveal");

        // Assert
        $response->assertForbidden();
    }

    #[Test]
    public function show_returns_session_details(): void
    {
        // Arrange
        $session = VoteSession::factory()->open()->create(['room_id' => $this->room->id]);

        // Act
        $response = $this->getJson("/api/sessions/$session->id");

        // Assert
        $response->assertOk()
                 ->assertJsonStructure([
                     'id',
                     'room_id',
                     'story_title',
                     'status',
                 ]);
    }

    #[Test]
    public function show_hides_votes_for_non_host_in_open_session(): void
    {
        // Arrange
        $session = VoteSession::factory()->open()->create(['room_id' => $this->room->id]);
        
        \App\Models\Vote::factory()->create([
            'vote_session_id' => $session->id,
            'user_id' => $this->user->id,
            'value' => '3',
        ]);

        Sanctum::actingAs($this->otherUser);

        // Act
        $response = $this->getJson("/api/sessions/$session->id");

        // Assert
        $response->assertOk();
        $this->assertArrayNotHasKey('votes', $response->json());
    }

    #[Test]
    public function show_returns_votes_for_host_in_open_session(): void
    {
        // Arrange
        $session = VoteSession::factory()->open()->create(['room_id' => $this->room->id]);
        
        \App\Models\Vote::factory()->create([
            'vote_session_id' => $session->id,
            'user_id' => $this->user->id,
            'value' => '3',
        ]);

        // Act
        $response = $this->getJson("/api/sessions/$session->id");

        // Assert
        $response->assertOk()
                 ->assertJsonStructure(['id', 'votes']);
    }

    #[Test]
    public function show_returns_votes_for_non_host_in_revealed_session(): void
    {
        // Arrange
        $session = VoteSession::factory()->revealed()->create(['room_id' => $this->room->id]);
        
        \App\Models\Vote::factory()->create([
            'vote_session_id' => $session->id,
            'user_id' => $this->user->id,
            'value' => '3',
        ]);

        Sanctum::actingAs($this->otherUser);

        // Act
        $response = $this->getJson("/api/sessions/$session->id");

        // Assert
        $response->assertOk()
                 ->assertJsonStructure(['id', 'votes']);
    }
}
