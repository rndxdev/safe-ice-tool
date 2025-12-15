<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use HasFactory;

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
