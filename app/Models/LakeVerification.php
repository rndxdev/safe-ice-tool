<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LakeVerification extends Model
{
    protected $fillable = [
        'user_id',
        'lake_id',
        'verdict',
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
