<?php

namespace Tests\Feature;

use App\Models\IceReport;
use App\Models\Lake;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pins the CURRENT behaviour of ice-report voting and the -5 auto-hide moderation.
 *
 * NOTE: voting currently has no per-user dedup (audit finding C1). The
 * `test_current_behaviour_*` cases below intentionally document that a single
 * user can move the score arbitrarily. When C1 is fixed (one vote per user),
 * those cases are expected to change.
 */
class IceReportVotingTest extends TestCase
{
    use RefreshDatabase;

    private function report(array $overrides = []): IceReport
    {
        $lake = Lake::factory()->create();

        return IceReport::factory()->for($lake)->create($overrides);
    }

    public function test_guests_cannot_vote(): void
    {
        $report = $this->report();

        $this->post(route('reports.downvote', $report))->assertRedirect(route('login'));

        $this->assertSame(0, $report->fresh()->downvotes);
    }

    public function test_upvote_increments_upvotes(): void
    {
        $report = $this->report(['upvotes' => 0]);

        $this->actingAs(User::factory()->create())
            ->from(route('lakes.show', $report->lake->slug))
            ->post(route('reports.upvote', $report))
            ->assertRedirect(route('lakes.show', $report->lake->slug));

        $this->assertSame(1, $report->fresh()->upvotes);
    }

    public function test_downvote_increments_downvotes(): void
    {
        $report = $this->report(['downvotes' => 0]);

        $this->actingAs(User::factory()->create())
            ->from(route('lakes.show', $report->lake->slug))
            ->post(route('reports.downvote', $report))
            ->assertRedirect(route('lakes.show', $report->lake->slug));

        $this->assertSame(1, $report->fresh()->downvotes);
    }

    public function test_report_is_hidden_and_flagged_when_net_score_reaches_minus_five(): void
    {
        // Start at -4 (already 4 downvotes), one more crosses the threshold.
        $report = $this->report(['downvotes' => 4, 'is_hidden' => false, 'is_flagged' => false]);

        $this->actingAs(User::factory()->create())
            ->from(route('lakes.show', $report->lake->slug))
            ->post(route('reports.downvote', $report));

        $fresh = $report->fresh();
        $this->assertSame(5, $fresh->downvotes);
        $this->assertTrue((bool) $fresh->is_hidden);
        $this->assertTrue((bool) $fresh->is_flagged);
    }

    public function test_report_stays_visible_just_above_the_threshold(): void
    {
        // -4 net after this downvote (3 existing -> 4), not yet hidden.
        $report = $this->report(['downvotes' => 3, 'is_hidden' => false]);

        $this->actingAs(User::factory()->create())
            ->from(route('lakes.show', $report->lake->slug))
            ->post(route('reports.downvote', $report));

        $fresh = $report->fresh();
        $this->assertSame(4, $fresh->downvotes);
        $this->assertFalse((bool) $fresh->is_hidden);
    }

    public function test_upvotes_offset_downvotes_in_the_moderation_score(): void
    {
        // 5 downvotes but 5 upvotes => net 0, must stay visible.
        $report = $this->report(['upvotes' => 4, 'downvotes' => 5, 'is_hidden' => false]);

        $this->actingAs(User::factory()->create())
            ->from(route('lakes.show', $report->lake->slug))
            ->post(route('reports.upvote', $report));

        $fresh = $report->fresh();
        $this->assertSame(5, $fresh->upvotes);
        $this->assertSame(5, $fresh->downvotes);
        $this->assertFalse((bool) $fresh->is_hidden);
    }

    /**
     * CURRENT (pre-C1) behaviour: there is no per-user vote dedup, so the same
     * user voting repeatedly stacks downvotes and can unilaterally hide a report.
     * This is the vulnerability C1 will close — update this test when it does.
     */
    public function test_current_behaviour_single_user_can_stack_downvotes_to_hide_a_report(): void
    {
        $report = $this->report(['downvotes' => 0, 'is_hidden' => false]);
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)
                ->from(route('lakes.show', $report->lake->slug))
                ->post(route('reports.downvote', $report));
        }

        $fresh = $report->fresh();
        $this->assertSame(5, $fresh->downvotes);
        $this->assertTrue((bool) $fresh->is_hidden);
    }
}
