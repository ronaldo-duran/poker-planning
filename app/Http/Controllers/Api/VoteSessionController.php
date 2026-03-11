<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vote\CreateSessionRequest;
use App\Http\Requests\Vote\SubmitVoteRequest;
use App\Models\Room;
use App\Models\VoteSession;
use App\Repositories\Contracts\VoteSessionRepositoryInterface;
use App\Services\VoteSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoteSessionController extends Controller
{
    public function __construct(
        private readonly VoteSessionService $voteSessionService,
        private readonly VoteSessionRepositoryInterface $sessionRepository,
    ) {}

    public function store(CreateSessionRequest $request, Room $room): JsonResponse
    {
        $this->authorize('update', $room);
        $session = $this->voteSessionService->createSession($room, $request->validated());

        return response()->json($session, 201);
    }

    public function submitVote(SubmitVoteRequest $request, VoteSession $voteSession): JsonResponse
    {
        $user = $request->user();

        if (! $voteSession->room->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $this->voteSessionService->submitVote(
            $voteSession->id,
            $user->id,
            $request->validated()['value'],
        );

        return response()->json(['message' => 'Vote submitted.']);
    }

    public function reveal(Request $request, VoteSession $voteSession): JsonResponse
    {
        $this->authorize('update', $voteSession->room);
        $session = $this->voteSessionService->revealVotes($voteSession);

        return response()->json($session);
    }

    public function show(Request $request, VoteSession $voteSession): JsonResponse
    {
        $user = $request->user();
        $isMember = $voteSession->room->users()->where('user_id', $user->id)->exists();
        $isHost = $voteSession->room->host_id === $user->id;

        if (! ($isMember || $isHost)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $session = $this->sessionRepository->findById($voteSession->id);

        // Hide individual votes from non-host members until the session is revealed,
        // to preserve blind-voting behaviour.
        if ($session->status !== 'revealed' && ! $isHost) {
            $session->unsetRelation('votes');
        }

        return response()->json($session);
    }
}
