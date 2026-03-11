<?php

namespace App\Repositories;

use App\Models\VoteSession;
use App\Repositories\Contracts\VoteSessionRepositoryInterface;

class VoteSessionRepository implements VoteSessionRepositoryInterface
{
    public function findById(int $id): ?VoteSession
    {
        return VoteSession::with('votes.user')->find($id);
    }

    public function create(array $data): VoteSession
    {
        return VoteSession::create($data);
    }

    public function update(VoteSession $session, array $data): VoteSession
    {
        $session->update($data);

        return $session->fresh();
    }

    public function getActiveForRoom(int $roomId): ?VoteSession
    {
        return VoteSession::where('room_id', $roomId)
            ->where('status', 'open')
            ->latest()
            ->first();
    }
}
