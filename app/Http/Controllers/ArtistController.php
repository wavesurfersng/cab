<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Track;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtistController extends Controller
{
    public function profile($stageName)
    {
        $artist = Artist::with(['user', 'tracks', 'videos', 'posts'])
            ->where('stage_name', $stageName)
            ->firstOrFail();

        $posts = $artist->posts()
            ->with(['comments.user', 'likes'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $isFollowing = false; // Implement follow system if needed

        return view('artist.profile', compact('artist', 'posts', 'isFollowing'));
    }

    public function dashboard()
    {
        if (!Auth::user()->isArtist()) {
            return redirect()->route('home')->with('error', 'Access denied.');
        }

        $artist = Auth::user()->artist;
        $tracks = $artist->tracks()->orderBy('created_at', 'desc')->get();
        $videos = $artist->videos()->orderBy('created_at', 'desc')->get();
        $wallet = $artist->user->wallet;

        $totalViews = $artist->total_views;
        $totalEarnings = $artist->total_earnings;

        return view('artist.dashboard', compact(
            'artist',
            'tracks',
            'videos',
            'wallet',
            'totalViews',
            'totalEarnings'
        ));
    }

    public function createPost(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'media' => 'nullable|file|max:10240',
        ]);

        $artist = Auth::user()->artist;

        $mediaUrl = null;
        $mediaType = null;

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $mediaUrl = $file->store('posts', 'public');
            
            if (in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif'])) {
                $mediaType = 'image';
            } elseif (in_array($file->getClientOriginalExtension(), ['mp4', 'mov', 'avi'])) {
                $mediaType = 'video';
            } elseif (in_array($file->getClientOriginalExtension(), ['mp3', 'wav'])) {
                $mediaType = 'audio';
            }
        }

        Post::create([
            'artist_id' => $artist->id,
            'content' => $request->content,
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
        ]);

        return back()->with('success', 'Post created successfully!');
    }

    public function like(Request $request)
    {
        $request->validate([
            'likeable_type' => 'required|in:App\\Models\\Track,App\\Models\\Video,App\\Models\\Post',
            'likeable_id' => 'required|integer',
        ]);

        $user = Auth::user();
        
        $existingLike = Like::where('user_id', $user->id)
            ->where('likeable_type', $request->likeable_type)
            ->where('likeable_id', $request->likeable_id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            
            // Decrement likes count on the model
            $model = $request->likeable_type::find($request->likeable_id);
            if ($model && $model instanceof Post) {
                $model->decrement('likes_count');
            }
            
            return response()->json(['liked' => false]);
        }

        Like::create([
            'user_id' => $user->id,
            'likeable_type' => $request->likeable_type,
            'likeable_id' => $request->likeable_id,
        ]);

        // Increment likes count on the model
        $model = $request->likeable_type::find($request->likeable_id);
        if ($model && $model instanceof Post) {
            $model->increment('likes_count');
        }

        return response()->json(['liked' => true]);
    }

    public function comment(Request $request)
    {
        $request->validate([
            'commentable_type' => 'required|in:App\\Models\\Track,App\\Models\\Video,App\\Models\\Post',
            'commentable_id' => 'required|integer',
            'content' => 'required|string|max:500',
        ]);

        Comment::create([
            'user_id' => Auth::id(),
            'commentable_type' => $request->commentable_type,
            'commentable_id' => $request->commentable_id,
            'content' => $request->content,
        ]);

        // Increment comments count if it's a post
        if ($request->commentable_type === 'App\\Models\\Post') {
            $post = Post::find($request->commentable_id);
            if ($post) {
                $post->increment('comments_count');
            }
        }

        return back()->with('success', 'Comment added successfully!');
    }
}
