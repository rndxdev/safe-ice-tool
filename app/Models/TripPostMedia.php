<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Public URL for the stored file (uploads go to the 'public' disk).
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->path ? Storage::disk('public')->url($this->path) : null,
        );
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(TripPost::class, 'trip_post_id');
    }
}
