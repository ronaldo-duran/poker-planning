<?php

namespace Tests\Feature\Room;

use App\Models\Room;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoomApiTest extends TestCase
{
    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    #[Test]
    public function index_returns_paginated_user_rooms(): void
    {
        // Arrange
        Room::factory()->count(3)->create(['host_id' => $this->user->id]);
        Room::factory()->count(2)->create(); // Not hosted by this user

        // Act
        $response = $this->getJson('/api/rooms');

        // Assert
        $response->assertOk()
                 ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'code', 'host_id']]]);
        
        // Verify pagination data exists
        $this->assertIsArray($response->json('data'));
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    #[Test]
    public function store_creates_room_as_authenticated_user(): void
    {
        // Arrange
        $data = [
            'name' => 'Sprint Planning',
            'card_config' => [0, 1, 2, 3, 5, 8, 13, 21, '?'],
        ];

        // Act
        $response = $this->postJson('/api/rooms', $data);

        // Assert
        $response->assertCreated()
                 ->assertJsonStructure([
                     'id',
                     'name',
                     'code',
                     'host_id',
                     'state',
                 ]);

        $this->assertEquals('Sprint Planning', $response->json('name'));
        $this->assertEquals($this->user->id, $response->json('host_id'));
        $this->assertDatabaseHas('rooms', [
            'name' => 'Sprint Planning',
            'host_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function store_requires_room_name(): void
    {
        // Act
        $response = $this->postJson('/api/rooms', []);

        // Assert
        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function show_returns_room_details_for_host(): void
    {
        // Arrange
        $room = Room::factory()->create(['host_id' => $this->user->id]);
        $room->users()->attach($this->user->id, ['role' => 'host']);

        // Act
        $response = $this->getJson("/api/rooms/$room->id");

        // Assert
        $response->assertOk()
                 ->assertJsonStructure([
                     'id',
                     'name',
                     'code',
                     'host_id',
                     'users' => [
                         '*' => ['id', 'name', 'pivot'],
                     ],
                 ]);
    }

    #[Test]
    public function show_returns_room_details_for_member(): void
    {
        // Arrange
        $room = Room::factory()->create(['host_id' => $this->otherUser->id]);
        $room->users()->attach($this->otherUser->id, ['role' => 'host']);
        $room->users()->attach($this->user->id, ['role' => 'voter']);

        // Act
        $response = $this->getJson("/api/rooms/$room->id");

        // Assert
        $response->assertOk()
                 ->assertJsonStructure(['id', 'name', 'code']);
    }

    #[Test]
    public function show_returns_404_for_non_member(): void
    {
        // Arrange
        $room = Room::factory()->create(['host_id' => $this->otherUser->id]);
        $room->users()->attach($this->otherUser->id, ['role' => 'host']);

        // Act
        $response = $this->getJson("/api/rooms/$room->id");

        // Assert
        $response->assertNotFound();
    }

    #[Test]
    public function join_adds_user_to_room(): void
    {
        // Arrange
        $room = Room::factory()->create(['host_id' => $this->otherUser->id]);
        $room->users()->attach($this->otherUser->id, ['role' => 'host']);

        // Act
        $response = $this->postJson("/api/rooms/join/{$room->code}");

        // Assert
        $response->assertOk()
                 ->assertJsonStructure(['id', 'name', 'code', 'users']);

        $this->assertTrue($room->fresh()->users()->where('user_id', $this->user->id)->exists());
    }

    #[Test]
    public function join_fails_with_invalid_code(): void
    {
        // Act
        $response = $this->postJson('/api/rooms/join/INVALID');

        // Assert
        $response->assertNotFound();
    }

    #[Test]
    public function leave_sets_user_offline(): void
    {
        // Arrange
        $room = Room::factory()->create(['host_id' => $this->otherUser->id]);
        $room->users()->attach($this->otherUser->id, ['role' => 'host']);
        $room->users()->attach($this->user->id, ['role' => 'voter', 'is_online' => true]);

        // Act
        $response = $this->postJson("/api/rooms/$room->id/leave");

        // Assert
        $response->assertOk();

        $refresh = $room->fresh()->users()->where('user_id', $this->user->id)->first();
        $this->assertFalse($refresh->pivot->is_online);
    }

    #[Test]
    public function update_state_changes_room_state(): void
    {
        // Arrange
        $room = Room::factory()->create(['host_id' => $this->user->id]);
        $room->users()->attach($this->user->id, ['role' => 'host']);

        // Act
        $response = $this->patchJson("/api/rooms/$room->id/state", [
            'state' => 'voting',
        ]);

        // Assert
        $response->assertOk()
                 ->assertJson(['state' => 'voting']);

        $this->assertEquals('voting', $room->fresh()->state);
    }

    #[Test]
    public function update_state_requires_authorization(): void
    {
        // Arrange
        $room = Room::factory()->create(['host_id' => $this->otherUser->id]);
        $room->users()->attach($this->otherUser->id, ['role' => 'host']);
        $room->users()->attach($this->user->id, ['role' => 'voter']);

        // Act
        $response = $this->patchJson("/api/rooms/$room->id/state", [
            'state' => 'voting',
        ]);

        // Assert
        $response->assertForbidden();
    }

    #[Test]
    public function toggle_emojis_toggles_emojis_blocked(): void
    {
        // Arrange
        $room = Room::factory()->create(['host_id' => $this->user->id, 'emojis_blocked' => false]);
        $room->users()->attach($this->user->id, ['role' => 'host']);

        // Act
        $response = $this->patchJson("/api/rooms/$room->id/toggle-emojis");

        // Assert
        $response->assertOk()
                 ->assertJson(['emojis_blocked' => true]);

        $this->assertTrue($room->fresh()->emojis_blocked);
    }

    #[Test]
    public function toggle_emojis_requires_authorization(): void
    {
        // Arrange
        $room = Room::factory()->create(['host_id' => $this->otherUser->id]);
        $room->users()->attach($this->otherUser->id, ['role' => 'host']);
        $room->users()->attach($this->user->id, ['role' => 'voter']);

        // Act
        $response = $this->patchJson("/api/rooms/$room->id/toggle-emojis");

        // Assert
        $response->assertForbidden();
    }
}
