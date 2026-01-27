<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedReaction extends Model
{
    protected $fillable = [
        'user_id',
        'item_type',
        'item_id',
        'reaction',
    ];
}
