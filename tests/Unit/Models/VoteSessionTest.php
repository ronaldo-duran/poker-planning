<?php

namespace Tests\Unit\Models;

use App\Models\Room;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteSession;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(VoteSession::class)]
class VoteSessionTest extends TestCase
{
    #[Test]
    public function vote_session_belongs_to_room(): void
    {
        // Arrange
        $room = Room::factory()->create();
        $session = VoteSession::factory()->create(['room_id' => $room->id]);

        // Act
        $sessionRoom = $session->room;

        // Assert
        $this->assertInstanceOf(Room::class, $sessionRoom);
        $this->assertEquals($room->id, $sessionRoom->id);
    }

    #[Test]
    public function vote_session_has_many_votes(): void
    {
        // Arrange
        $session = VoteSession::factory()->create();
        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            Vote::factory()->create([
                'vote_session_id' => $session->id,
                'user_id' => $user->id,
            ]);
        }

        // Act
        $votes = $session->votes;

        // Assert
        $this->assertCount(3, $votes);
    }

    #[Test]
    public function vote_session_fillable_attributes(): void
    {
        // Arrange & Act
        $session = VoteSession::create([
            'room_id' => Room::factory()->create()->id,
            'story_title' => 'User Authentication',
            'story_description' => 'Implement user login',
            'status' => 'open',
        ]);

        // Assert
        $this->assertEquals('User Authentication', $session->story_title);
        $this->assertEquals('Implement user login', $session->story_description);
        $this->assertEquals('open', $session->status);
    }

    #[Test]
    public function vote_session_average_calculation(): void
    {
        // Arrange
        $session = VoteSession::factory()->revealed()->create();

        // Act & Assert
        $this->assertNotNull($session->average);
        // Average can be string (decimal from PostgreSQL) or numeric
        $this->assertTrue(
            is_numeric($session->average),
            'Average should be numeric (string or number)'
        );
    }
}
