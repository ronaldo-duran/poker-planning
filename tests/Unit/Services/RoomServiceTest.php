<?php

namespace Tests\Unit\Services;

use App\Models\Room;
use App\Models\User;
use App\Services\RoomService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(RoomService::class)]
class RoomServiceTest extends TestCase
{
    private RoomService $roomService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roomService = app(RoomService::class);
    }

    #[Test]
    public function create_room_stores_room_in_database(): void
    {
        // Arrange
        $user = User::factory()->create();
        $data = [
            'name' => 'Planning Session',
            'card_config' => [0, 1, 2, 3, 5, 8, 13, 21, '?'],
        ];

        // Act
        $room = $this->roomService->createRoom($user, $data);

        // Assert
        $this->assertInstanceOf(Room::class, $room);
        $this->assertEquals('Planning Session', $room->name);
        $this->assertEquals($user->id, $room->host_id);
        $this->assertDatabaseHas('rooms', [
            'name' => 'Planning Session',
            'host_id' => $user->id,
        ]);
    }

    #[Test]
    public function create_room_generates_unique_code(): void
    {
        // Arrange
        $user = User::factory()->create();
        $data = ['name' => 'Room 1'];

        // Act
        $room1 = $this->roomService->createRoom($user, $data);
        $room2 = $this->roomService->createRoom($user, $data);

        // Assert
        $this->assertNotEquals($room1->code, $room2->code);
        $this->assertEquals(8, strlen($room1->code));
        $this->assertEquals(8, strlen($room2->code));
    }

    #[Test]
    public function create_room_attaches_host_as_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $data = ['name' => 'Test Room'];

        // Act
        $room = $this->roomService->createRoom($user, $data);

        // Assert
        $this->assertTrue($room->users()->where('user_id', $user->id)->exists());
        $role = $room->users()->where('user_id', $user->id)->first()->pivot->role;
        $this->assertEquals('host', $role);
    }

    #[Test]
    public function join_room_adds_user_to_room(): void
    {
        // Arrange
        $host = User::factory()->create();
        $joiner = User::factory()->create();
        $room = $this->roomService->createRoom($host, ['name' => 'Test Room']);

        // Act
        $joined = $this->roomService->joinRoom($joiner, $room->code);

        // Assert
        $this->assertTrue($joined->users()->where('user_id', $joiner->id)->exists());
        $role = $joined->users()->where('user_id', $joiner->id)->first()->pivot->role;
        $this->assertEquals('voter', $role);
    }

    #[Test]
    public function join_room_sets_user_online(): void
    {
        // Arrange
        $host = User::factory()->create();
        $joiner = User::factory()->create();
        $room = $this->roomService->createRoom($host, ['name' => 'Test Room']);

        // Act
        $joined = $this->roomService->joinRoom($joiner, $room->code);

        // Assert
        $isOnline = $joined->users()->where('user_id', $joiner->id)->first()->pivot->is_online;
        $this->assertTrue($isOnline);
    }

    #[Test]
    public function join_room_fails_with_invalid_code(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Assert
        $this->expectException(\Throwable::class);

        // Act
        $this->roomService->joinRoom($user, 'INVALID');
    }

    #[Test]
    public function leave_room_sets_user_offline(): void
    {
        // Arrange
        $host = User::factory()->create();
        $user = User::factory()->create();
        $room = $this->roomService->createRoom($host, ['name' => 'Test Room']);
        $this->roomService->joinRoom($user, $room->code);

        // Act
        $this->roomService->leaveRoom($user, $room);

        // Assert
        $refresh = $room->fresh();
        $isOnline = $refresh->users()->where('user_id', $user->id)->first()->pivot->is_online;
        $this->assertFalse($isOnline);
    }

    #[Test]
    public function update_state_changes_room_state(): void
    {
        // Arrange
        $user = User::factory()->create();
        $room = $this->roomService->createRoom($user, ['name' => 'Test Room']);

        // Act
        $updated = $this->roomService->updateState($room, 'voting');

        // Assert
        $this->assertEquals('voting', $updated->state);
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'state' => 'voting',
        ]);
    }

    #[Test]
    public function toggle_emojis_toggles_emojis_blocked(): void
    {
        // Arrange
        $user = User::factory()->create();
        $room = $this->roomService->createRoom($user, ['name' => 'Test Room']);
        $this->assertFalse($room->emojis_blocked);

        // Act
        $updated = $this->roomService->toggleEmojis($room);

        // Assert
        $this->assertTrue($updated->emojis_blocked);

        // Act again
        $updated = $this->roomService->toggleEmojis($updated);

        // Assert
        $this->assertFalse($updated->emojis_blocked);
    }
}
