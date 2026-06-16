<?php

namespace Database\Factories;

use App\Models\IceReport;
use App\Models\Lake;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IceReport>
 */
class IceReportFactory extends Factory
{
    protected $model = IceReport::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lake_id' => Lake::factory(),
            'user_id' => User::factory(),
            'lat' => fake()->latitude(43, 49),
            'lng' => fake()->longitude(-97, -82),
            'thickness_inches' => fake()->randomFloat(1, 2, 18),
            'ice_type' => fake()->randomElement(['clear', 'snow', 'cloudy']),
            'traffic_type' => fake()->randomElement(['foot', 'atv', 'snowmobile']),
            'has_slush' => false,
            'has_pressure_cracks' => false,
            'notes' => null,
            'upvotes' => 0,
            'downvotes' => 0,
            'is_flagged' => false,
            'is_hidden' => false,
        ];
    }

    /**
     * Thin, unsafe ice (drives the safety score down).
     */
    public function thinIce(): static
    {
        return $this->state(fn (array $attributes) => [
            'thickness_inches' => fake()->randomFloat(1, 0.5, 3.5),
        ]);
    }

    /**
     * Thick, safe ice (drives the safety score up).
     */
    public function thickIce(): static
    {
        return $this->state(fn (array $attributes) => [
            'thickness_inches' => fake()->randomFloat(1, 8, 18),
        ]);
    }

    public function slushy(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_slush' => true,
        ]);
    }

    public function cracked(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_pressure_cracks' => true,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hidden' => true,
        ]);
    }

    /**
     * An anonymous report (user later deleted / not attributed).
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
