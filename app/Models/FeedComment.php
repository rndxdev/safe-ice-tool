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
