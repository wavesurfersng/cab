<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\Video;
use App\Models\Post;
use App\Models\Artist;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredTracks = Track::with('artist')
            ->where('is_published', true)
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        $featuredVideos = Video::with('artist')
            ->where('is_published', true)
            ->orderBy('views', 'desc')
            ->limit(5)
            ->get();

        $recentPosts = Post::with(['artist.user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $trendingArtists = Artist::with('user')
            ->where('is_verified', true)
            ->orderBy('total_views', 'desc')
            ->limit(5)
            ->get();

        return view('home.index', compact(
            'featuredTracks',
            'featuredVideos',
            'recentPosts',
            'trendingArtists'
        ));
    }

    public function track($id)
    {
        $track = Track::with(['artist.user', 'comments.user'])->findOrFail($id);
        
        // Increment views
        $track->incrementViews();
        
        $relatedTracks = Track::where('artist_id', $track->artist_id)
            ->where('id', '!=', $track->id)
            ->where('is_published', true)
            ->limit(5)
            ->get();

        return view('home.track', compact('track', 'relatedTracks'));
    }

    public function video($id)
    {
        $video = Video::with(['artist.user', 'comments.user'])->findOrFail($id);
        
        // Increment views
        $video->incrementViews();
        
        $relatedVideos = Video::where('artist_id', $video->artist_id)
            ->where('id', '!=', $video->id)
            ->where('is_published', true)
            ->limit(5)
            ->get();

        return view('home.video', compact('video', 'relatedVideos'));
    }
}
