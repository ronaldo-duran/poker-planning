<?php

namespace Tests\Unit\Models;

use App\Models\Room;
use App\Models\User;
use App\Models\VoteSession;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(Room::class)]
class RoomTest extends TestCase
{
    #[Test]
    public function room_belongs_to_host(): void
    {
        // Arrange
        $host = User::factory()->create();
        $room = Room::factory()->create(['host_id' => $host->id]);

        // Act
        $hostFromRoom = $room->host;

        // Assert
        $this->assertInstanceOf(User::class, $hostFromRoom);
        $this->assertEquals($host->id, $hostFromRoom->id);
    }

    #[Test]
    public function room_has_many_users(): void
    {
        // Arrange
        $room = Room::factory()->create();
        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            $room->users()->attach($user->id, ['role' => 'voter']);
        }

        // Act
        $roomUsers = $room->users;

        // Assert
        $this->assertCount(3, $roomUsers);
    }

    #[Test]
    public function room_has_many_vote_sessions(): void
    {
        // Arrange
        $room = Room::factory()->create();
        VoteSession::factory()->count(3)->create(['room_id' => $room->id]);

        // Act
        $sessions = $room->voteSessions;

        // Assert
        $this->assertCount(3, $sessions);
    }

    #[Test]
    public function room_has_active_sessions(): void
    {
        // Arrange
        $room = Room::factory()->create();
        VoteSession::factory()->count(2)->open()->create(['room_id' => $room->id]);
        VoteSession::factory()->count(1)->closed()->create(['room_id' => $room->id]);

        // Act
        $activeSessions = $room->activeSessions;

        // Assert
        $this->assertCount(2, $activeSessions);
    }

    #[Test]
    public function room_card_config_is_array(): void
    {
        // Arrange & Act
        $room = Room::factory()->create([
            'card_config' => [0, 1, 2, 3, 5, 8, 13, 21, '?'],
        ]);

        // Assert
        $this->assertIsArray($room->card_config);
        $this->assertContains('?', $room->card_config);
    }

    #[Test]
    public function room_fillable_attributes(): void
    {
        // Arrange & Act
        $host = User::factory()->create();
        $room = Room::create([
            'name' => 'Test Room',
            'code' => 'TESTCODE',
            'host_id' => $host->id,
            'card_config' => [0, 1, 2, 3],
            'state' => 'waiting',
        ]);

        // Assert
        $this->assertEquals('Test Room', $room->name);
        $this->assertEquals('TESTCODE', $room->code);
        $this->assertEquals('waiting', $room->state);
    }
}
