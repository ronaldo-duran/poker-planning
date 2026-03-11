<?php

namespace Tests\Feature\Emoji;

use App\Models\Room;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmojiApiTest extends TestCase
{
    protected User $sender;
    protected User $target;
    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sender = User::factory()->create();
        $this->target = User::factory()->create();
        
        $this->room = Room::factory()->create(['host_id' => $this->sender->id]);
        $this->room->users()->attach($this->sender->id, ['role' => 'host']);
        $this->room->users()->attach($this->target->id, ['role' => 'voter']);
    }

    #[Test]
    public function send_emoji_broadcasts_emoji_to_room(): void
    {
        // Arrange
        Sanctum::actingAs($this->sender);
        $data = [
            'emoji' => '👍',
            'target_id' => $this->target->id,
        ];

        // Act
        $response = $this->postJson("/api/rooms/{$this->room->id}/emojis", $data);

        // Assert
        $response->assertCreated()
                 ->assertJsonStructure([
                     'id',
                     'room_id',
                     'emoji',
                     'sender_id',
                     'target_id',
                 ]);

        $this->assertDatabaseHas('emojis', [
            'room_id' => $this->room->id,
            'sender_id' => $this->sender->id,
            'emoji' => '👍',
            'target_id' => $this->target->id,
        ]);
    }

    #[Test]
    public function send_emoji_without_target(): void
    {
        // Arrange
        Sanctum::actingAs($this->sender);
        // Arrange
        $data = [
            'emoji' => '🎉',
        ];

        // Act
        $response = $this->postJson("/api/rooms/{$this->room->id}/emojis", $data);

        // Assert
        $response->assertCreated()
                 ->assertJson([
                     'emoji' => '🎉',
                     'target_id' => null,
                 ]);

        $this->assertDatabaseHas('emojis', [
            'emoji' => '🎉',
            'target_id' => null,
        ]);
    }

    #[Test]
    public function send_emoji_fails_if_user_not_member(): void
    {
        // Arrange
        Sanctum::actingAs(User::factory()->create());
        // Arrange
        $other = User::factory()->create();

        // Act
        $response = $this->postJson("/api/rooms/{$this->room->id}/emojis", [
            'emoji' => '👍',
        ]);

        // Assert
        $response->assertForbidden();
    }

    #[Test]
    public function send_emoji_fails_if_emojis_blocked(): void
    {
        // Arrange
        Sanctum::actingAs($this->sender);
        // Arrange
        $this->room->update(['emojis_blocked' => true]);

        // Act
        $response = $this->postJson("/api/rooms/{$this->room->id}/emojis", [
            'emoji' => '👍',
        ]);

        // Assert
        $response->assertForbidden();
    }

    #[Test]
    public function send_emoji_requires_authentication(): void
    {
        // Act - No authentication provided
        // Act - Remove authentication
        $response = $this->postJson("/api/rooms/{$this->room->id}/emojis", [
            'emoji' => '👍',
        ]);

        // Assert
        $response->assertUnauthorized();
    }
}
