<?php

namespace Database\Factories;

use App\Models\Emoji;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Emoji>
 */
class EmojiFactory extends Factory
{
    protected $model = Emoji::class;

    public function definition(): array
    {
        $emojis = ['👍', '❤️', '🎉', '😂', '🔥', '👏', '💯', '🚀'];

        return [
            'room_id' => Room::factory(),
            'sender_id' => User::factory(),
            'target_id' => null,
            'emoji' => $this->faker->randomElement($emojis),
        ];
    }

    public function withTarget(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'target_id' => $user->id,
        ]);
    }
}
