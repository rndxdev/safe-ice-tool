<?php

namespace Database\Factories;

use App\Models\Lake;
use App\Models\TripPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripPost>
 */
class TripPostFactory extends Factory
{
    protected $model = TripPost::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'trip_id' => null,
            'lake_id' => Lake::factory(),
            'caption' => fake()->sentence(),
            'people_tags' => [],
            'location_tags' => [],
            'is_public' => true,
            'share_token' => null,
        ];
    }

    /**
     * A private post (not visible in the public feed / share routes).
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * A post with a public share token.
     */
    public function shared(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'share_token' => Str::random(32),
        ]);
    }
}
