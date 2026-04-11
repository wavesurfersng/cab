<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $fillable = [
        'user_id',
        'stage_name',
        'bio',
        'genre',
        'profile_picture',
        'cover_image',
        'is_verified',
        'total_views',
        'total_earnings',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'total_views' => 'integer',
        'total_earnings' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tracks()
    {
        return $this->hasMany(Track::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getProfileUrlAttribute()
    {
        return route('artist.profile', $this->stage_name);
    }
}
