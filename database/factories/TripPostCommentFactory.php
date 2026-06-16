<?php

namespace Database\Factories;

use App\Models\TripPost;
use App\Models\TripPostComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripPostComment>
 */
class TripPostCommentFactory extends Factory
{
    protected $model = TripPostComment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_post_id' => TripPost::factory(),
            'user_id' => User::factory(),
            'body' => fake()->sentence(),
        ];
    }
}
