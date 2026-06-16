<?php

namespace App\Models;

use App\Services\FeedInteractionCleanup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lake extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Lake $lake) {
            $cleanup = app(FeedInteractionCleanup::class);

            // Ice reports and trips are removed by the DB cascade (no model
            // events), so purge their interactions before the lake is deleted.
            foreach ($lake->iceReports()->pluck('id') as $reportId) {
                $cleanup->purgeItem('report', (int) $reportId);
            }

            foreach ($lake->trips()->pluck('id') as $tripId) {
                $cleanup->purgeItem('trip_share', (int) $tripId);
            }

            $cleanup->purgeItem('lake', $lake->id);
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'lat',
        'lng',
        'region',
        'state',
        'county',
        'is_active',
        'status',
        'created_by_user_id',
    ];

    public function iceReports(): HasMany
    {
        return $this->hasMany(IceReport::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lake_user_favorites')
            ->withTimestamps();
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
