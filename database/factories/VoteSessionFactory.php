<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\VoteSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VoteSession>
 */
class VoteSessionFactory extends Factory
{
    protected $model = VoteSession::class;

    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'story_title' => $this->faker->sentence(),
            'story_description' => $this->faker->paragraph(),
            'status' => 'open',
            'average' => null,
            'revealed_at' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'average' => null,
            'revealed_at' => null,
        ]);
    }

    public function revealed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'revealed',
            'average' => $this->faker->randomFloat(2, 1, 21),
            'revealed_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'average' => null,
            'revealed_at' => null,
        ]);
    }
}
