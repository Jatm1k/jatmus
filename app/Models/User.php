<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'name',
        'username',
        'balance',
        'is_premium',
        'premium_until',
    ];

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }
}
