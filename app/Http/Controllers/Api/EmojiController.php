<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emoji\SendEmojiRequest;
use App\Models\Room;
use App\Services\EmojiService;
use Illuminate\Http\JsonResponse;

class EmojiController extends Controller
{
    public function __construct(private readonly EmojiService $emojiService) {}

    public function send(SendEmojiRequest $request, Room $room): JsonResponse
    {
        $emoji = $this->emojiService->sendEmoji(
            $room,
            $request->user(),
            $request->validated()['emoji'],
            $request->validated()['target_id'] ?? null,
        );

        return response()->json($emoji, 201);
    }
}
