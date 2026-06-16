<?php

namespace Database\Factories;

use App\Models\IceReport;
use App\Models\IceReportVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IceReportVote>
 */
class IceReportVoteFactory extends Factory
{
    protected $model = IceReportVote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ice_report_id' => IceReport::factory(),
            'user_id' => User::factory(),
            'value' => IceReportVote::UP,
        ];
    }

    public function up(): static
    {
        return $this->state(fn (array $attributes) => ['value' => IceReportVote::UP]);
    }

    public function down(): static
    {
        return $this->state(fn (array $attributes) => ['value' => IceReportVote::DOWN]);
    }
}
