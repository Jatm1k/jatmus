<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'streak',
        'last_claimed_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
