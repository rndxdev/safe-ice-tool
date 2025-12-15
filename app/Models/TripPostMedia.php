<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripPostMedia extends Model
{
    protected $table = 'trip_post_media';

    protected $fillable = [
        'trip_post_id',
        'path',
        'mime',
        'size',
        'sort_order',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(TripPost::class, 'trip_post_id');
    }
}
