<?php

namespace App\Models;

use App\Services\FeedInteractionCleanup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Trip $trip) {
            app(FeedInteractionCleanup::class)->purgeItem('trip_share', $trip->id);
        });
    }

    protected $fillable = [
        'user_id',
        'lake_id',
        'trip_date',
        'time_of_day',
        'min_thickness_inches',
        'avoid_slush',
        'avoid_pressure_cracks',
        'target_species',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lake(): BelongsTo
    {
        return $this->belongsTo(Lake::class);
    }
}
