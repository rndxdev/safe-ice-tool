<?php

namespace Tests\Unit;

use App\Models\IceReport;
use App\Models\Lake;
use App\Services\LakeSafetyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pins the scoring behaviour of LakeSafetyService.
 *
 * Score model (base 50, clamped 0-100):
 *   thickness: avg>=8 +45, avg>=4 +20, else -20
 *   slush:     ratio>0.6 -20, >0.3 -10
 *   cracks:    ratio>0.5 -25, >0.2 -10
 *   hidden:    ratio>0.4 -15, >0.2 -5
 *   downvotes: per-report>2 -10, >0.5 -5
 *   labels:    >=75 stable, >=50 mixed, else inconsistent
 */
class LakeSafetyServiceTest extends TestCase
{
    use RefreshDatabase;

    private LakeSafetyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LakeSafetyService;
    }

    /**
     * Create $count reports on $lake, all in the recent window, with overrides applied.
     */
    private function reports(Lake $lake, int $count, array $overrides = []): void
    {
        IceReport::factory()->count($count)->for($lake)->create($overrides);
    }

    public function test_returns_not_enough_data_when_no_reports_exist(): void
    {
        $lake = Lake::factory()->create();

        $result = $this->service->computeForLake($lake);

        $this->assertNull($result['score']);
        $this->assertSame('Not enough data', $result['label']);
        $this->assertSame(0, $result['metrics']['report_count']);
        $this->assertSame(10, $result['metrics']['window_days']);
    }

    public function test_thick_clean_ice_scores_high_and_is_stable(): void
    {
        $lake = Lake::factory()->create();
        $this->reports($lake, 3, ['thickness_inches' => 10]);

        $result = $this->service->computeForLake($lake);

        $this->assertSame(95, $result['score']); // 50 + 45
        $this->assertSame('Stable freeze pattern', $result['label']);
    }

    public function test_medium_ice_scores_mixed(): void
    {
        $lake = Lake::factory()->create();
        $this->reports($lake, 3, ['thickness_inches' => 6]);

        $result = $this->service->computeForLake($lake);

        $this->assertSame(70, $result['score']); // 50 + 20
        $this->assertSame('Mixed conditions', $result['label']);
    }

    public function test_thin_ice_scores_low_and_is_inconsistent(): void
    {
        $lake = Lake::factory()->create();
        $this->reports($lake, 3, ['thickness_inches' => 3]);

        $result = $this->service->computeForLake($lake);

        $this->assertSame(30, $result['score']); // 50 - 20
        $this->assertSame('Inconsistent conditions', $result['label']);
    }

    public function test_moderate_slush_ratio_applies_small_penalty(): void
    {
        $lake = Lake::factory()->create();
        $this->reports($lake, 2, ['thickness_inches' => 6, 'has_slush' => true]);
        $this->reports($lake, 2, ['thickness_inches' => 6, 'has_slush' => false]);

        // ratio 0.5 (>0.3) => -10
        $this->assertSame(60, $this->service->computeForLake($lake)['score']);
    }

    public function test_high_slush_ratio_applies_large_penalty_at_label_boundary(): void
    {
        $lake = Lake::factory()->create();
        $this->reports($lake, 3, ['thickness_inches' => 6, 'has_slush' => true]);
        $this->reports($lake, 1, ['thickness_inches' => 6, 'has_slush' => false]);

        $result = $this->service->computeForLake($lake);

        // ratio 0.75 (>0.6) => -20, lands exactly on the 50 label boundary
        $this->assertSame(50, $result['score']);
        $this->assertSame('Mixed conditions', $result['label']);
    }

    public function test_moderate_crack_ratio_applies_small_penalty(): void
    {
        $lake = Lake::factory()->create();
        $this->reports($lake, 2, ['thickness_inches' => 6, 'has_pressure_cracks' => true]);
        $this->reports($lake, 2, ['thickness_inches' => 6, 'has_pressure_cracks' => false]);

        // ratio 0.5 (>0.2, not >0.5) => -10
        $this->assertSame(60, $this->service->computeForLake($lake)['score']);
    }

    public function test_high_crack_ratio_applies_large_penalty(): void
    {
        $lake = Lake::factory()->create();
        $this->reports($lake, 3, ['thickness_inches' => 6, 'has_pressure_cracks' => true]);
        $this->reports($lake, 1, ['thickness_inches' => 6, 'has_pressure_cracks' => false]);

        // ratio 0.75 (>0.5) => -25
        $this->assertSame(45, $this->service->computeForLake($lake)['score']);
    }

    public function test_moderate_hidden_ratio_applies_small_penalty(): void
    {
        $lake = Lake::factory()->create();
        $this->reports($lake, 2, ['thickness_inches' => 6, 'is_hidden' => true]);
        $this->reports($lake, 3, ['thickness_inches' => 6, 'is_hidden' => false]);

        // ratio 0.4 (>0.2, not >0.4) => -5
        $this->assertSame(65, $this->service->computeForLake($lake)['score']);
    }

    public function test_high_hidden_ratio_applies_large_penalty(): void
    {
        $lake = Lake::factory()->create();
        $this->reports($lake, 3, ['thickness_inches' => 6, 'is_hidden' => true]);
        $this->reports($lake, 2, ['thickness_inches' => 6, 'is_hidden' => false]);

        // ratio 0.6 (>0.4) => -15
        $this->assertSame(55, $this->service->computeForLake($lake)['score']);
    }

    public function test_moderate_downvotes_apply_small_penalty(): void
    {
        $lake = Lake::factory()->create();
        $this->reports($lake, 2, ['thickness_inches' => 6, 'downvotes' => 1]);

        // 2 downvotes / 2 reports = 1.0 (>0.5) => -5
        $this->assertSame(65, $this->service->computeForLake($lake)['score']);
    }

    public function test_heavy_downvotes_apply_large_penalty(): void
    {
        $lake = Lake::factory()->create();
        $this->reports($lake, 2, ['thickness_inches' => 6, 'downvotes' => 3]);

        // 6 downvotes / 2 reports = 3.0 (>2) => -10
        $this->assertSame(60, $this->service->computeForLake($lake)['score']);
    }

    public function test_score_is_clamped_to_zero_when_everything_is_bad(): void
    {
        $lake = Lake::factory()->create();
        $this->reports($lake, 4, [
            'thickness_inches' => 2,        // -20
            'has_slush' => true,            // ratio 1.0 => -20
            'has_pressure_cracks' => true,  // ratio 1.0 => -25
            'is_hidden' => true,            // ratio 1.0 => -15
            'downvotes' => 3,               // 3.0/report => -10
        ]);

        $result = $this->service->computeForLake($lake);

        // 50 - 90 = -40, clamped to 0
        $this->assertSame(0, $result['score']);
        $this->assertSame('Inconsistent conditions', $result['label']);
    }

    public function test_only_reports_within_the_window_are_counted(): void
    {
        $lake = Lake::factory()->create();

        // Recent thick report — should count.
        $this->reports($lake, 1, ['thickness_inches' => 10]);

        // Old thin report (35 days ago) — should be excluded from the 10-day window.
        $old = IceReport::factory()->for($lake)->create(['thickness_inches' => 2]);
        $old->forceFill(['created_at' => now()->subDays(35)])->saveQuietly();

        $result = $this->service->computeForLake($lake);

        $this->assertSame(1, $result['metrics']['report_count']);
        $this->assertSame(95, $result['score']); // only the thick report counts
    }

    public function test_window_size_is_configurable(): void
    {
        $lake = Lake::factory()->create();

        $report = IceReport::factory()->for($lake)->create(['thickness_inches' => 10]);
        $report->forceFill(['created_at' => now()->subDays(20)])->saveQuietly();

        // Default 10-day window excludes it...
        $this->assertSame(0, $this->service->computeForLake($lake)['metrics']['report_count']);

        // ...but a 30-day window includes it.
        $wide = $this->service->computeForLake($lake, 30);
        $this->assertSame(1, $wide['metrics']['report_count']);
        $this->assertSame(30, $wide['metrics']['window_days']);
    }
}
