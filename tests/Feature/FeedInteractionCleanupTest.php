<?php

namespace Tests\Feature;

use App\Models\CommentLike;
use App\Models\FeedAcknowledgement;
use App\Models\FeedComment;
use App\Models\FeedReaction;
use App\Models\IceReport;
use App\Models\Lake;
use App\Models\Trip;
use App\Models\TripPost;
use App\Models\TripPostComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * C2: deleting an entity must remove the polymorphic feed interactions keyed to
 * it (which have no foreign keys), otherwise they orphan and bleed onto a future
 * entity that reuses the id.
 */
class FeedInteractionCleanupTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Attach a full set of feed interactions (ack, reaction, comment, and a like
     * on that comment) to one feed item.
     */
    private function attachInteractions(string $type, int $id): FeedComment
    {
        FeedAcknowledgement::create([
            'user_id' => $this->user->id,
            'item_type' => $type,
            'item_id' => $id,
            'acknowledged_at' => now(),
        ]);

        FeedReaction::create([
            'user_id' => $this->user->id,
            'item_type' => $type,
            'item_id' => $id,
            'reaction' => 'like',
        ]);

        $comment = FeedComment::create([
            'user_id' => $this->user->id,
            'item_type' => $type,
            'item_id' => $id,
            'body' => 'nice ice',
        ]);

        CommentLike::create([
            'user_id' => $this->user->id,
            'comment_type' => 'feed_comment',
            'comment_id' => $comment->id,
        ]);

        return $comment;
    }

    private function assertNoFeedInteractionsRemain(): void
    {
        $this->assertDatabaseCount('feed_acknowledgements', 0);
        $this->assertDatabaseCount('feed_reactions', 0);
        $this->assertDatabaseCount('feed_comments', 0);
        $this->assertDatabaseCount('comment_likes', 0);
    }

    public function test_deleting_an_ice_report_purges_its_interactions(): void
    {
        $report = IceReport::factory()->create();
        $this->attachInteractions('report', $report->id);

        $report->delete();

        $this->assertNoFeedInteractionsRemain();
    }

    public function test_deleting_a_trip_purges_its_interactions(): void
    {
        $trip = Trip::factory()->create();
        $this->attachInteractions('trip_share', $trip->id);

        $trip->delete();

        $this->assertNoFeedInteractionsRemain();
    }

    public function test_deleting_a_trip_post_comment_purges_feed_and_like_rows(): void
    {
        $comment = TripPostComment::factory()->create();

        // A like directly on the trip-post comment.
        CommentLike::create([
            'user_id' => $this->user->id,
            'comment_type' => 'trip_post_comment',
            'comment_id' => $comment->id,
        ]);
        // Feed-level interactions referencing the comment.
        $this->attachInteractions('comment', $comment->id);

        $comment->delete();

        $this->assertNoFeedInteractionsRemain();
    }

    public function test_deleting_a_trip_post_purges_post_and_cascaded_comment_interactions(): void
    {
        $post = TripPost::factory()->create();
        $comment = TripPostComment::factory()->create(['trip_post_id' => $post->id]);

        $this->attachInteractions('post', $post->id);
        $this->attachInteractions('comment', $comment->id);
        CommentLike::create([
            'user_id' => $this->user->id,
            'comment_type' => 'trip_post_comment',
            'comment_id' => $comment->id,
        ]);

        $post->delete();

        // The comment is removed by the DB cascade; its interactions must go too.
        $this->assertDatabaseCount('trip_post_comments', 0);
        $this->assertNoFeedInteractionsRemain();
    }

    public function test_deleting_a_lake_purges_lake_report_and_trip_interactions(): void
    {
        $lake = Lake::factory()->create();
        $report = IceReport::factory()->for($lake)->create();
        $trip = Trip::factory()->for($lake)->create();

        $this->attachInteractions('lake', $lake->id);
        $this->attachInteractions('report', $report->id);
        $this->attachInteractions('trip_share', $trip->id);

        $lake->delete();

        // Cascade removed the reports and trips...
        $this->assertDatabaseCount('ice_reports', 0);
        $this->assertDatabaseCount('trips', 0);
        // ...and none of their interactions are left behind.
        $this->assertNoFeedInteractionsRemain();
    }

    public function test_unrelated_interactions_are_left_intact(): void
    {
        $deleted = IceReport::factory()->create();
        $kept = IceReport::factory()->create();

        $this->attachInteractions('report', $deleted->id);
        $this->attachInteractions('report', $kept->id);

        $deleted->delete();

        // The kept report keeps its full set: 1 ack + 1 reaction + 1 comment + 1 like.
        $this->assertDatabaseCount('feed_acknowledgements', 1);
        $this->assertDatabaseCount('feed_reactions', 1);
        $this->assertDatabaseCount('feed_comments', 1);
        $this->assertDatabaseCount('comment_likes', 1);
        $this->assertDatabaseHas('feed_reactions', ['item_type' => 'report', 'item_id' => $kept->id]);
    }
}
