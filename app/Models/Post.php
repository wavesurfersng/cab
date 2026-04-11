<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'artist_id',
        'content',
        'media_url',
        'media_type',
        'likes_count',
        'comments_count',
    ];

    protected $casts = [
        'likes_count' => 'integer',
        'comments_count' => 'integer',
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function incrementLikesCount()
    {
        $this->increment('likes_count');
    }

    public function incrementCommentsCount()
    {
        $this->increment('comments_count');
    }
}
