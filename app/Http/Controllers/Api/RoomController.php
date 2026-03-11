<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Room\CreateRoomRequest;
use App\Http\Requests\Room\UpdateRoomStateRequest;
use App\Models\Room;
use App\Repositories\Contracts\RoomRepositoryInterface;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(
        private readonly RoomService $roomService,
        private readonly RoomRepositoryInterface $roomRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $rooms = $this->roomRepository->paginateForUser($request->user()->id);

        return response()->json($rooms);
    }

    public function store(CreateRoomRequest $request): JsonResponse
    {
        $room = $this->roomService->createRoom($request->user(), $request->validated(), $request->file('logo'));

        return response()->json($room, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $room = $this->roomRepository->findById($id);

        if (! $room) {
            return response()->json(['message' => 'Room not found.'], 404);
        }

        $user = $request->user();
        $isHost = $room->host_id === $user->id;
        $isMember = $room->users()->where('users.id', $user->id)->exists();

        if (! $isHost && ! $isMember) {
            return response()->json(['message' => 'Room not found.'], 404);
        }

        return response()->json($room->load('users', 'host', 'voteSessions'));
    }

    public function join(Request $request, string $code): JsonResponse
    {
        $room = $this->roomService->joinRoom($request->user(), $code);

        return response()->json($room);
    }

    public function leave(Request $request, Room $room): JsonResponse
    {
        $this->roomService->leaveRoom($request->user(), $room);

        return response()->json(['message' => 'Left room.']);
    }

    public function updateState(UpdateRoomStateRequest $request, Room $room): JsonResponse
    {
        $this->authorize('update', $room);
        $updated = $this->roomService->updateState($room, $request->validated()['state']);

        return response()->json($updated);
    }

    public function toggleEmojis(Request $request, Room $room): JsonResponse
    {
        $this->authorize('update', $room);
        $updated = $this->roomService->toggleEmojis($room);

        return response()->json($updated);
    }
}
