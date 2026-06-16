<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IceReportVote extends Model
{
    use HasFactory;

    public const UP = 1;

    public const DOWN = -1;

    protected $fillable = [
        'ice_report_id',
        'user_id',
        'value',
    ];

    protected $casts = [
        'value' => 'integer',
    ];

    public function iceReport(): BelongsTo
    {
        return $this->belongsTo(IceReport::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
