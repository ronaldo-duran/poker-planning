<?php

namespace App\Repositories;

use App\Models\Vote;
use App\Repositories\Contracts\VoteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class VoteRepository implements VoteRepositoryInterface
{
    public function findBySessionAndUser(int $sessionId, int $userId): ?Vote
    {
        return Vote::where('vote_session_id', $sessionId)
            ->where('user_id', $userId)
            ->first();
    }

    public function upsert(int $sessionId, int $userId, string $value): Vote
    {
        return Vote::updateOrCreate(
            ['vote_session_id' => $sessionId, 'user_id' => $userId],
            ['value' => $value]
        );
    }

    public function getForSession(int $sessionId): Collection
    {
        return Vote::with('user')
            ->where('vote_session_id', $sessionId)
            ->get();
    }
}
