<?php

namespace Database\Factories;

use App\Models\Lake;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
 */
class TripFactory extends Factory
{
    protected $model = Trip::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lake_id' => Lake::factory(),
            'trip_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'time_of_day' => fake()->randomElement(['morning', 'afternoon', 'evening']),
            'min_thickness_inches' => fake()->randomElement([null, 4, 6, 8]),
            'avoid_slush' => fake()->boolean(),
            'avoid_pressure_cracks' => fake()->boolean(),
            'target_species' => fake()->randomElement(['walleye', 'northern pike', 'perch', 'crappie']),
            'notes' => null,
        ];
    }

    /**
     * A publicly shareable trip with a share token.
     */
    public function shared(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'share_token' => Str::random(32),
        ]);
    }
}
