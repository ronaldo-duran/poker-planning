<?php

namespace Tests\Unit\Models;

use App\Models\Room;
use App\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(User::class)]
class UserTest extends TestCase
{
    #[Test]
    public function user_has_hosted_rooms(): void
    {
        // Arrange
        $user = User::factory()->create();
        Room::factory()->count(3)->create(['host_id' => $user->id]);

        // Act
        $hostedRooms = $user->hostedRooms;

        // Assert
        $this->assertCount(3, $hostedRooms);
    }

    #[Test]
    public function user_belongs_to_many_rooms(): void
    {
        // Arrange
        $user = User::factory()->create();
        $rooms = Room::factory()->count(2)->create();

        foreach ($rooms as $room) {
            $user->rooms()->attach($room->id, ['role' => 'voter']);
        }

        // Act
        $userRooms = $user->rooms;

        // Assert
        $this->assertCount(2, $userRooms);
    }

    #[Test]
    public function user_has_votes(): void
    {
        // Arrange
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $session1 = \App\Models\VoteSession::factory()->create(['room_id' => $room->id]);
        $session2 = \App\Models\VoteSession::factory()->create(['room_id' => $room->id]);
        
        \App\Models\Vote::factory()->create([
            'user_id' => $user->id,
            'vote_session_id' => $session1->id,
        ]);
        \App\Models\Vote::factory()->create([
            'user_id' => $user->id,
            'vote_session_id' => $session2->id,
        ]);

        // Act
        $votes = $user->votes;

        // Assert
        $this->assertCount(2, $votes);
    }

    #[Test]
    public function user_fillable_attributes(): void
    {
        // Arrange & Act
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar' => 'avatar-path',
            'bio' => 'Test bio',
        ]);

        // Assert
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('avatar-path', $user->avatar);
        $this->assertEquals('Test bio', $user->bio);
    }

    #[Test]
    public function user_password_is_hidden(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $hidden = $user->getHidden();

        // Assert
        $this->assertContains('password', $hidden);
    }
}
