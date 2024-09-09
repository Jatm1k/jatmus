<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Song extends Model
{
    use SoftDeletes;
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

    protected $with = ['user'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
