<?php

namespace Tests\Unit\Services;

use App\Models\Emoji;
use App\Models\Room;
use App\Models\User;
use App\Services\EmojiService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(EmojiService::class)]
class EmojiServiceTest extends TestCase
{
    private EmojiService $emojiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emojiService = app(EmojiService::class);
    }

    #[Test]
    public function send_emoji_creates_emoji_record(): void
    {
        // Arrange
        $sender = User::factory()->create();
        $room = Room::factory()->create();
        $room->users()->attach($sender->id, ['role' => 'voter']);

        // Act
        $emoji = $this->emojiService->sendEmoji($room, $sender, '👍');

        // Assert
        $this->assertInstanceOf(Emoji::class, $emoji);
        $this->assertEquals('👍', $emoji->emoji);
        $this->assertEquals($sender->id, $emoji->sender_id);
        $this->assertEquals($room->id, $emoji->room_id);
        $this->assertDatabaseHas('emojis', [
            'emoji' => '👍',
            'sender_id' => $sender->id,
            'room_id' => $room->id,
        ]);
    }

    #[Test]
    public function send_emoji_with_target_user(): void
    {
        // Arrange
        $sender = User::factory()->create();
        $target = User::factory()->create();
        $room = Room::factory()->create();
        $room->users()->attach($sender->id, ['role' => 'voter']);
        $room->users()->attach($target->id, ['role' => 'voter']);

        // Act
        $emoji = $this->emojiService->sendEmoji($room, $sender, '❤️', $target->id);

        // Assert
        $this->assertEquals($target->id, $emoji->target_id);
        $this->assertDatabaseHas('emojis', [
            'target_id' => $target->id,
            'sender_id' => $sender->id,
        ]);
    }

    #[Test]
    public function send_emoji_fails_if_sender_not_in_room(): void
    {
        // Arrange
        $sender = User::factory()->create();
        $room = Room::factory()->create();

        // Assert
        $this->expectException(\Throwable::class);

        // Act
        $this->emojiService->sendEmoji($room, $sender, '👍');
    }

    #[Test]
    public function send_emoji_fails_if_emojis_are_blocked(): void
    {
        // Arrange
        $sender = User::factory()->create();
        $room = Room::factory()->create(['emojis_blocked' => true]);
        $room->users()->attach($sender->id, ['role' => 'voter']);

        // Assert
        $this->expectException(\Throwable::class);

        // Act
        $this->emojiService->sendEmoji($room, $sender, '👍');
    }
}
