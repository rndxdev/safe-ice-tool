<?php

namespace Database\Factories;

use App\Models\Lake;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lake>
 */
class LakeFactory extends Factory
{
    protected $model = Lake::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city().' Lake';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            // Upper Midwest-ish coordinates where ice fishing happens.
            'lat' => fake()->latitude(43, 49),
            'lng' => fake()->longitude(-97, -82),
            'region' => fake()->randomElement(['Minnesota', 'Wisconsin', 'Michigan']),
            'is_active' => true,
            'status' => 'approved',
        ];
    }

    /**
     * A lake still awaiting community verification.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * An inactive (soft-disabled) lake.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
