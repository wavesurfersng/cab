<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'artist_id',
        'title',
        'description',
        'file_path',
        'thumbnail',
        'storage_type',
        'cloud_url',
        'duration',
        'views',
        'earnings',
        'is_published',
    ];

    protected $casts = [
        'views' => 'integer',
        'earnings' => 'decimal:2',
        'is_published' => 'boolean',
        'duration' => 'integer',
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

    public function getFileUrlAttribute()
    {
        if ($this->storage_type === 'local') {
            return asset('uploads/videos/' . $this->file_path);
        }
        return $this->cloud_url;
    }

    public function incrementViews()
    {
        $this->increment('views');
        
        // Calculate earnings: NGN 5000 for 50,000 views = NGN 0.1 per view
        $earningsPerView = 5000 / 50000;
        $newEarnings = $earningsPerView;
        
        $this->increment('earnings', $newEarnings);
        
        // Update artist totals
        $this->artist->increment('total_views');
        $this->artist->increment('total_earnings', $newEarnings);
        
        // Add to wallet
        $wallet = $this->artist->user->wallet;
        if ($wallet) {
            $wallet->increment('pending_balance', $newEarnings);
            $wallet->increment('total_earned', $newEarnings);
        }
    }
}
