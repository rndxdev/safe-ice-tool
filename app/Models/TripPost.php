<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripPost extends Model
{
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
