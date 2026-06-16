<?php

namespace App\Services;

use App\Models\CommentLike;
use App\Models\FeedAcknowledgement;
use App\Models\FeedComment;
use App\Models\FeedReaction;

/**
 * Removes the hand-rolled polymorphic feed interactions that reference an item
 * only by (item_type, item_id) — they have no foreign keys, so without this
 * cleanup they orphan when their parent entity is deleted, and (because ids are
 * reused per table) bleed onto a future entity that reuses the id.
 */
class FeedInteractionCleanup
{
    /**
     * Purge every interaction attached to a single feed item: acknowledgements,
     * reactions, feed comments (and the likes on those comments). When the item
     * is itself a trip-post comment, its own likes are cleared too.
     */
    public function purgeItem(string $itemType, int $itemId): void
    {
        $feedCommentIds = FeedComment::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->pluck('id')
            ->all();

        $this->deleteFeedCommentLikes($feedCommentIds);

        FeedComment::where('item_type', $itemType)->where('item_id', $itemId)->delete();
        FeedReaction::where('item_type', $itemType)->where('item_id', $itemId)->delete();
        FeedAcknowledgement::where('item_type', $itemType)->where('item_id', $itemId)->delete();

        if ($itemType === 'comment') {
            CommentLike::where('comment_type', 'trip_post_comment')
                ->where('comment_id', $itemId)
                ->delete();
        }
    }

    /**
     * Delete a set of feed comments and the likes on them. Used when a feed
     * comment is removed directly (its own model event).
     */
    public function purgeFeedComments(array $feedCommentIds): void
    {
        if (empty($feedCommentIds)) {
            return;
        }

        $this->deleteFeedCommentLikes($feedCommentIds);
        FeedComment::whereIn('id', $feedCommentIds)->delete();
    }

    private function deleteFeedCommentLikes(array $feedCommentIds): void
    {
        if (empty($feedCommentIds)) {
            return;
        }

        CommentLike::where('comment_type', 'feed_comment')
            ->whereIn('comment_id', $feedCommentIds)
            ->delete();
    }
}
