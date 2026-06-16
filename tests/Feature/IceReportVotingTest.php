<?php

namespace Tests\Feature;

use App\Models\IceReport;
use App\Models\IceReportVote;
use App\Models\Lake;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers ice-report voting after the C1 fix: one vote per user (deduped via a
 * dedicated votes table), with counts recomputed from that table and the -5
 * net-score auto-hide preserved.
 */
class IceReportVotingTest extends TestCase
{
    use RefreshDatabase;

    private function report(array $overrides = []): IceReport
    {
        $lake = Lake::factory()->create();

        return IceReport::factory()->for($lake)->create($overrides);
    }

    private function voteAs(User $user, IceReport $report, string $direction)
    {
        return $this->actingAs($user)
            ->from(route('lakes.show', $report->lake->slug))
            ->post(route("reports.{$direction}", $report));
    }

    public function test_guests_cannot_vote(): void
    {
        $report = $this->report();

        $this->post(route('reports.downvote', $report))->assertRedirect(route('login'));

        $this->assertSame(0, $report->fresh()->downvotes);
        $this->assertDatabaseCount('ice_report_votes', 0);
    }

    public function test_upvote_records_a_single_vote_and_counts_it(): void
    {
        $report = $this->report();
        $user = User::factory()->create();

        $this->voteAs($user, $report, 'upvote')
            ->assertRedirect(route('lakes.show', $report->lake->slug));

        $this->assertSame(1, $report->fresh()->upvotes);
        $this->assertDatabaseHas('ice_report_votes', [
            'ice_report_id' => $report->id,
            'user_id' => $user->id,
            'value' => IceReportVote::UP,
        ]);
    }

    public function test_downvote_records_a_single_vote(): void
    {
        $report = $this->report();
        $user = User::factory()->create();

        $this->voteAs($user, $report, 'downvote');

        $this->assertSame(1, $report->fresh()->downvotes);
    }

    public function test_pressing_the_same_direction_again_toggles_the_vote_off(): void
    {
        $report = $this->report();
        $user = User::factory()->create();

        $this->voteAs($user, $report, 'upvote');
        $this->voteAs($user, $report, 'upvote');

        $this->assertSame(0, $report->fresh()->upvotes);
        $this->assertDatabaseCount('ice_report_votes', 0);
    }

    public function test_voting_the_opposite_direction_switches_the_vote(): void
    {
        $report = $this->report();
        $user = User::factory()->create();

        $this->voteAs($user, $report, 'downvote');
        $this->voteAs($user, $report, 'upvote');

        $fresh = $report->fresh();
        $this->assertSame(0, $fresh->downvotes);
        $this->assertSame(1, $fresh->upvotes);
        $this->assertDatabaseCount('ice_report_votes', 1);
    }

    public function test_a_single_user_cannot_stack_votes_to_hide_a_report(): void
    {
        // This is the C1 fix: the old behaviour let one user downvote repeatedly.
        $report = $this->report();
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->voteAs($user, $report, 'downvote');
        }

        $fresh = $report->fresh();
        $this->assertSame(1, $fresh->downvotes);
        $this->assertFalse((bool) $fresh->is_hidden);
        $this->assertDatabaseCount('ice_report_votes', 1);
    }

    public function test_report_is_hidden_and_flagged_when_five_distinct_users_downvote(): void
    {
        $report = $this->report();

        User::factory()->count(5)->create()
            ->each(fn (User $user) => $this->voteAs($user, $report, 'downvote'));

        $fresh = $report->fresh();
        $this->assertSame(5, $fresh->downvotes);
        $this->assertTrue((bool) $fresh->is_hidden);
        $this->assertTrue((bool) $fresh->is_flagged);
    }

    public function test_report_stays_visible_at_four_distinct_downvotes(): void
    {
        $report = $this->report();

        User::factory()->count(4)->create()
            ->each(fn (User $user) => $this->voteAs($user, $report, 'downvote'));

        $fresh = $report->fresh();
        $this->assertSame(4, $fresh->downvotes);
        $this->assertFalse((bool) $fresh->is_hidden);
    }

    public function test_upvotes_offset_downvotes_in_the_moderation_score(): void
    {
        $report = $this->report();

        // 5 downvotes alone would hit the -5 threshold, but 2 upvotes keep the
        // net score at -3, so the report must stay visible.
        User::factory()->count(2)->create()
            ->each(fn (User $user) => $this->voteAs($user, $report, 'upvote'));
        User::factory()->count(5)->create()
            ->each(fn (User $user) => $this->voteAs($user, $report, 'downvote'));

        $fresh = $report->fresh();
        $this->assertSame(2, $fresh->upvotes);
        $this->assertSame(5, $fresh->downvotes);
        $this->assertFalse((bool) $fresh->is_hidden);
    }
}
