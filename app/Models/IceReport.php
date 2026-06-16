<?php

namespace App\Models;

use App\Services\FeedInteractionCleanup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IceReport extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (IceReport $report) {
            app(FeedInteractionCleanup::class)->purgeItem('report', $report->id);
        });
    }

    protected $fillable = [
        'lake_id',
        'user_id',
        'lat',
        'lng',
        'thickness_inches',
        'ice_type',
        'traffic_type',
        'has_slush',
        'has_pressure_cracks',
        'notes',
    ];

    // is_flagged / is_hidden are deliberately NOT fillable — they are moderation
    // flags set only by server-side logic (see IceReportController::syncVoteCounts).

    public function lake(): BelongsTo
    {
        return $this->belongsTo(Lake::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(IceReportVote::class);
    }
}
