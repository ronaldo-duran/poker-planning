<?php

namespace App\Repositories\Contracts;

use App\Models\Vote;
use Illuminate\Database\Eloquent\Collection;

interface VoteRepositoryInterface
{
    public function findBySessionAndUser(int $sessionId, int $userId): ?Vote;

    public function upsert(int $sessionId, int $userId, string $value): Vote;

    public function getForSession(int $sessionId): Collection;
}
