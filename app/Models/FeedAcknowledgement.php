<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedAcknowledgement extends Model
{
    protected $fillable = [
        'user_id',
        'item_type',
        'item_id',
        'acknowledged_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];
}
