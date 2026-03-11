<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vote;
use App\Models\VoteSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    protected $model = Vote::class;

    public function definition(): array
    {
        $values = [0, 1, 2, 3, 5, 8, 13, 21, '?'];

        return [
            'vote_session_id' => VoteSession::factory(),
            'user_id' => User::factory(),
            'value' => $this->faker->randomElement($values),
        ];
    }

    public function withValue(string|int $value): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $value,
        ]);
    }
}
