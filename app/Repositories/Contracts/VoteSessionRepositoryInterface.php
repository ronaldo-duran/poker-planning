<?php

namespace App\Repositories\Contracts;

use App\Models\VoteSession;

interface VoteSessionRepositoryInterface
{
    public function findById(int $id): ?VoteSession;

    public function create(array $data): VoteSession;

    public function update(VoteSession $session, array $data): VoteSession;

    public function getActiveForRoom(int $roomId): ?VoteSession;
}
