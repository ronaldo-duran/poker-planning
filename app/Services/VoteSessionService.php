<?php

namespace App\Services;

use App\Events\RevealStarted;
use App\Events\SessionStarted;
use App\Events\VoteSubmitted;
use App\Models\Room;
use App\Models\VoteSession;
use App\Repositories\Contracts\VoteRepositoryInterface;
use App\Repositories\Contracts\VoteSessionRepositoryInterface;

class VoteSessionService
{
    public function __construct(
        private readonly VoteSessionRepositoryInterface $sessionRepository,
        private readonly VoteRepositoryInterface $voteRepository,
    ) {}

    public function createSession(Room $room, array $data): VoteSession
    {
        // Close any open sessions
        $active = $this->sessionRepository->getActiveForRoom($room->id);
        if ($active) {
            $this->sessionRepository->update($active, ['status' => 'closed']);
        }

        $session = $this->sessionRepository->create([
            'room_id' => $room->id,
            'story_title' => $data['story_title'] ?? null,
            'story_description' => $data['story_description'] ?? null,
            'status' => 'open',
        ]);

        broadcast(new SessionStarted($session))->toOthers();

        return $session;
    }

    public function submitVote(int $sessionId, int $userId, string $value): void
    {
        $session = $this->sessionRepository->findById($sessionId);

        if (! $session || $session->status !== 'open') {
            abort(422, 'Voting session is not open.');
        }

        $vote = $this->voteRepository->upsert($sessionId, $userId, $value);

        broadcast(new VoteSubmitted($session, $userId))->toOthers();
    }

    public function revealVotes(VoteSession $session): VoteSession
    {
        $votes = $this->voteRepository->getForSession($session->id);

        $numericVotes = $votes->filter(fn ($v) => is_numeric($v->value))->pluck('value');
        $average = $numericVotes->isNotEmpty()
            ? round($numericVotes->avg(), 2)
            : null;

        $updated = $this->sessionRepository->update($session, [
            'status' => 'revealed',
            'average' => $average,
            'revealed_at' => now(),
        ]);

        broadcast(new RevealStarted($session, $votes->toArray(), $average));

        return $updated->load('votes.user');
    }
}
