<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripPostComment extends Model
{
    use HasFactory;

    protected $table = 'trip_post_comments';

    protected $fillable = [
        'trip_post_id',
        'user_id',
        'parent_id',
        'body',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(TripPost::class, 'trip_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TripPostComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TripPostComment::class, 'parent_id')->orderBy('created_at');
    }
}
