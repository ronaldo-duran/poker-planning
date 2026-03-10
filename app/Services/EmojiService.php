<?php

namespace App\Services;

use App\Events\EmojiSent;
use App\Models\Emoji;
use App\Models\Room;
use App\Models\User;

class EmojiService
{
    public function sendEmoji(Room $room, User $sender, string $emoji, ?int $targetId = null): Emoji
    {
        if ($room->emojis_blocked) {
            abort(403, 'Emojis are blocked in this room.');
        }

        $emojiRecord = Emoji::create([
            'room_id' => $room->id,
            'sender_id' => $sender->id,
            'target_id' => $targetId,
            'emoji' => $emoji,
        ]);

        broadcast(new EmojiSent($room, $sender, $emoji, $targetId));

        return $emojiRecord;
    }
}
