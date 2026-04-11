<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_picture',
        'bio',
        'wallet_balance',
        'is_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'wallet_balance' => 'decimal:2',
        'is_verified' => 'boolean',
    ];

    public function artist()
    {
        return $this->hasOne(Artist::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function isArtist()
    {
        return $this->role === 'artist';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
