<?php

namespace App\Models;

use App\Services\FeedInteractionCleanup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripPost extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (TripPost $post) {
            $cleanup = app(FeedInteractionCleanup::class);

            // Comments are removed by the DB cascade, which does not fire their
            // model events — purge each comment's interactions here.
            foreach ($post->comments()->pluck('id') as $commentId) {
                $cleanup->purgeItem('comment', (int) $commentId);
            }

            $cleanup->purgeItem('post', $post->id);
        });
    }

    protected $fillable = [
        'user_id',
        'trip_id',
        'lake_id',
        'caption',
        'people_tags',
        'location_tags',
        'is_public',
        'share_token',
    ];

    protected $casts = [
        'people_tags' => 'array',
        'location_tags' => 'array',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function lake(): BelongsTo
    {
        return $this->belongsTo(Lake::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(TripPostMedia::class)->orderBy('sort_order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TripPostComment::class)->latest();
    }
}
