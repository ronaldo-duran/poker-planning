<?php

namespace Tests\Unit\Services;

use App\Models\Room;
use App\Models\User;
use App\Models\VoteSession;
use App\Services\VoteSessionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(VoteSessionService::class)]
class VoteSessionServiceTest extends TestCase
{
    private VoteSessionService $voteSessionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->voteSessionService = app(VoteSessionService::class);
    }

    #[Test]
    public function create_session_creates_session_in_database(): void
    {
        // Arrange
        $host = User::factory()->create();
        $room = Room::factory()->create(['host_id' => $host->id]);
        $data = [
            'story_title' => 'User login feature',
            'story_description' => 'As a user, I want to login',
        ];

        // Act
        $session = $this->voteSessionService->createSession($room, $data);

        // Assert
        $this->assertInstanceOf(VoteSession::class, $session);
        $this->assertEquals('open', $session->status);
        $this->assertEquals('User login feature', $session->story_title);
        $this->assertDatabaseHas('vote_sessions', [
            'room_id' => $room->id,
            'story_title' => 'User login feature',
        ]);
    }

    #[Test]
    public function create_session_closes_previous_open_sessions(): void
    {
        // Arrange
        $host = User::factory()->create();
        $room = Room::factory()->create(['host_id' => $host->id]);
        
        $session1 = $this->voteSessionService->createSession($room, ['story_title' => 'Task 1']);
        $this->assertEquals('open', $session1->status);

        // Act
        $session2 = $this->voteSessionService->createSession($room, ['story_title' => 'Task 2']);

        // Assert
        $session1Fresh = VoteSession::find($session1->id);
        $this->assertEquals('closed', $session1Fresh->status);
        $this->assertEquals('open', $session2->status);
    }

    #[Test]
    public function submit_vote_stores_vote_for_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $host = User::factory()->create();
        $room = Room::factory()->create(['host_id' => $host->id]);
        $room->users()->attach($user->id, ['role' => 'voter']);
        
        $session = $this->voteSessionService->createSession($room, ['story_title' => 'Task']);

        // Act
        $this->voteSessionService->submitVote($session->id, $user->id, '5');

        // Assert
        $this->assertDatabaseHas('votes', [
            'vote_session_id' => $session->id,
            'user_id' => $user->id,
            'value' => '5',
        ]);
    }

    #[Test]
    public function submit_vote_updates_existing_vote(): void
    {
        // Arrange
        $user = User::factory()->create();
        $host = User::factory()->create();
        $room = Room::factory()->create(['host_id' => $host->id]);
        $room->users()->attach($user->id, ['role' => 'voter']);
        
        $session = $this->voteSessionService->createSession($room, ['story_title' => 'Task']);
        $this->voteSessionService->submitVote($session->id, $user->id, '3');

        // Act
        $this->voteSessionService->submitVote($session->id, $user->id, '8');

        // Assert
        $votes = $room->voteSessions()->first()->votes()->where('user_id', $user->id)->get();
        $this->assertCount(1, $votes);
        $this->assertEquals('8', $votes->first()->value);
    }

    #[Test]
    public function submit_vote_fails_on_closed_session(): void
    {
        // Arrange
        $user = User::factory()->create();
        $host = User::factory()->create();
        $room = Room::factory()->create(['host_id' => $host->id]);
        $room->users()->attach($user->id, ['role' => 'voter']);
        
        $session = $this->voteSessionService->createSession($room, ['story_title' => 'Task']);
        $session->update(['status' => 'closed']);

        // Assert
        $this->expectException(\Throwable::class);

        // Act
        $this->voteSessionService->submitVote($session->id, $user->id, '5');
    }

    #[Test]
    public function reveal_votes_calculates_average_of_numeric_votes(): void
    {
        // Arrange
        $host = User::factory()->create();
        $room = Room::factory()->create(['host_id' => $host->id]);
        $users = User::factory()->count(3)->create();
        
        foreach ($users as $user) {
            $room->users()->attach($user->id, ['role' => 'voter']);
        }

        $session = $this->voteSessionService->createSession($room, ['story_title' => 'Task']);
        $this->voteSessionService->submitVote($session->id, $users[0]->id, '3');
        $this->voteSessionService->submitVote($session->id, $users[1]->id, '5');
        $this->voteSessionService->submitVote($session->id, $users[2]->id, '8');

        // Act
        $revealed = $this->voteSessionService->revealVotes($session);

        // Assert
        $this->assertEquals('revealed', $revealed->status);
        $this->assertEquals(5.33, $revealed->average); // (3 + 5 + 8) / 3
        $this->assertNotNull($revealed->revealed_at);
    }

    #[Test]
    public function reveal_votes_excludes_non_numeric_votes_from_average(): void
    {
        // Arrange
        $host = User::factory()->create();
        $room = Room::factory()->create(['host_id' => $host->id]);
        $users = User::factory()->count(3)->create();
        
        foreach ($users as $user) {
            $room->users()->attach($user->id, ['role' => 'voter']);
        }

        $session = $this->voteSessionService->createSession($room, ['story_title' => 'Task']);
        $this->voteSessionService->submitVote($session->id, $users[0]->id, '5');
        $this->voteSessionService->submitVote($session->id, $users[1]->id, '8');
        $this->voteSessionService->submitVote($session->id, $users[2]->id, '?');

        // Act
        $revealed = $this->voteSessionService->revealVotes($session);

        // Assert
        $this->assertEquals(6.5, $revealed->average); // (5 + 8) / 2
    }
}
