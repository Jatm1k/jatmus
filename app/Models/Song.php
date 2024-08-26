<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Song extends Model
{
    protected $fillable = [
        'user_id',
        'original_filename',
        'processed_filename',
        'processed_path',
        'original_path',
        'processed_url',
        'original_url',
        'effect'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
