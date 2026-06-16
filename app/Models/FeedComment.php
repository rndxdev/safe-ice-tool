<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedComment extends Model
{
    protected $fillable = [
        'user_id',
        'parent_id',
        'item_type',
        'item_id',
        'body',
    ];

    protected static function booted(): void
    {
        // When a feed comment is deleted directly, remove the likes on it.
        // (Bulk deletes from the cleanup service handle their own likes.)
        static::deleting(function (FeedComment $comment) {
            CommentLike::where('comment_type', 'feed_comment')
                ->where('comment_id', $comment->id)
                ->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(FeedComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(FeedComment::class, 'parent_id')->orderBy('created_at');
    }
}
