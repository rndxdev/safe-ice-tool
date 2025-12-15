<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IceReport extends Model
{
    use HasFactory;

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
    'is_flagged',
    'is_hidden',
];


    public function lake(): BelongsTo
    {
        return $this->belongsTo(Lake::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
