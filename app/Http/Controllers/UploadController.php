<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\Video;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        if (!Auth::user()->isArtist()) {
            return redirect()->route('home')->with('error', 'You need to be an artist to upload content.');
        }

        $storageType = Setting::get('default_storage', 'local');
        $artist = Auth::user()->artist;

        return view('uploads.create', compact('storageType', 'artist'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:track,video',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file',
            'cover_art' => 'nullable|image|max:2048',
        ]);

        $artist = Auth::user()->artist;
        $storageType = Setting::get('default_storage', 'local');
        $cloudUrl = null;

        if ($request->type === 'track') {
            $request->validate([
                'file' => 'mimes:mp3,wav,ogg,m4a|max:51200',
            ]);
        } else {
            $request->validate([
                'file' => 'mimes:mp4,mov,avi|max:512000',
            ]);
        }

        // Handle file upload based on storage type
        if ($storageType === 'local') {
            $folder = $request->type === 'track' ? 'music' : 'videos';
            $filename = time() . '_' . $request->file('file')->getClientOriginalName();
            $path = $request->file('file')->storeAs($folder, $filename, 'public');
        } elseif ($storageType === 'amazon') {
            // Amazon S3 upload logic
            $path = $request->file('file')->store($request->type . 's', 's3');
            $cloudUrl = Storage::disk('s3')->url($path);
        } elseif ($storageType === 'cloudinary') {
            // Cloudinary upload logic would go here
            // For now, store locally as fallback
            $folder = $request->type === 'track' ? 'music' : 'videos';
            $filename = time() . '_' . $request->file('file')->getClientOriginalName();
            $path = $request->file('file')->storeAs($folder, $filename, 'public');
        }

        // Handle cover art/thumbnail
        $coverPath = null;
        if ($request->hasFile('cover_art')) {
            $coverPath = $request->file('cover_art')->store('covers', 'public');
        }

        if ($request->type === 'track') {
            Track::create([
                'artist_id' => $artist->id,
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => $path,
                'cover_art' => $coverPath,
                'storage_type' => $storageType,
                'cloud_url' => $cloudUrl,
                'is_published' => true,
            ]);

            return redirect()->route('artist.dashboard')->with('success', 'Track uploaded successfully!');
        } else {
            Video::create([
                'artist_id' => $artist->id,
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => $path,
                'thumbnail' => $coverPath,
                'storage_type' => $storageType,
                'cloud_url' => $cloudUrl,
                'is_published' => true,
            ]);

            return redirect()->route('artist.dashboard')->with('success', 'Video uploaded successfully!');
        }
    }

    public function uploadToCloudinary(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        // Cloudinary upload implementation
        // This would use the Cloudinary SDK
        // For now, return a placeholder response
        
        return response()->json([
            'success' => false,
            'message' => 'Cloudinary integration requires API configuration'
        ]);
    }
}
