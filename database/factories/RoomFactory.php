<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'code' => strtoupper($this->faker->unique()->bothify('????####')),
            'logo' => null,
            'host_id' => User::factory(),
            'card_config' => [0, 1, 2, 3, 5, 8, 13, 21, '?'],
            'state' => 'waiting',
            'emojis_blocked' => false,
        ];
    }

    public function withLogoPath(string $path): static
    {
        return $this->state(fn (array $attributes) => [
            'logo' => $path,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'in_progress',
        ]);
    }

    public function emojisBlocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'emojis_blocked' => true,
        ]);
    }
}
