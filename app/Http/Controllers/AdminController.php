<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Artist;
use App\Models\Track;
use App\Models\Video;
use App\Models\Setting;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Access denied.');
        }

        $totalUsers = User::count();
        $totalArtists = Artist::count();
        $totalTracks = Track::count();
        $totalVideos = Video::count();
        $totalPayouts = Wallet::sum('total_earned');

        $recentArtists = Artist::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalArtists',
            'totalTracks',
            'totalVideos',
            'totalPayouts',
            'recentArtists'
        ));
    }

    public function settings()
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Access denied.');
        }

        $settings = [
            'default_storage' => Setting::get('default_storage', 'local'),
            'upload_fee' => Setting::get('upload_fee', '5000'),
            'flutterwave_public_key' => Setting::get('flutterwave_public_key', ''),
            'flutterwave_secret_key' => Setting::get('flutterwave_secret_key', ''),
            'aws_access_key_id' => Setting::get('aws_access_key_id', ''),
            'aws_secret_access_key' => Setting::get('aws_secret_access_key', ''),
            'aws_default_region' => Setting::get('aws_default_region', 'us-east-1'),
            'aws_bucket' => Setting::get('aws_bucket', ''),
            'cloudinary_cloud_name' => Setting::get('cloudinary_cloud_name', ''),
            'cloudinary_api_key' => Setting::get('cloudinary_api_key', ''),
            'cloudinary_api_secret' => Setting::get('cloudinary_api_secret', ''),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Access denied.');
        }

        $request->validate([
            'default_storage' => 'required|in:local,amazon,cloudinary',
            'upload_fee' => 'required|numeric|min:0',
        ]);

        Setting::set('default_storage', $request->default_storage);
        Setting::set('upload_fee', $request->upload_fee);
        Setting::set('flutterwave_public_key', $request->flutterwave_public_key ?? '');
        Setting::set('flutterwave_secret_key', $request->flutterwave_secret_key ?? '');
        Setting::set('aws_access_key_id', $request->aws_access_key_id ?? '');
        Setting::set('aws_secret_access_key', $request->aws_secret_access_key ?? '');
        Setting::set('aws_default_region', $request->aws_default_region ?? 'us-east-1');
        Setting::set('aws_bucket', $request->aws_bucket ?? '');
        Setting::set('cloudinary_cloud_name', $request->cloudinary_cloud_name ?? '');
        Setting::set('cloudinary_api_key', $request->cloudinary_api_key ?? '');
        Setting::set('cloudinary_api_secret', $request->cloudinary_api_secret ?? '');

        return back()->with('success', 'Settings updated successfully!');
    }

    public function verifyArtist($id)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Access denied.');
        }

        $artist = Artist::findOrFail($id);
        $artist->update(['is_verified' => true]);

        return back()->with('success', 'Artist verified successfully!');
    }

    public function users()
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Access denied.');
        }

        $users = User::with('artist', 'wallet')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users', compact('users'));
    }
}
