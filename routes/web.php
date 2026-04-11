<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/track/{id}', [HomeController::class, 'track'])->name('track.show');
Route::get('/video/{id}', [HomeController::class, 'video'])->name('video.show');

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Artist routes
Route::get('/artist/{stageName}', [ArtistController::class, 'profile'])->name('artist.profile');
Route::middleware(['auth'])->group(function () {
    Route::get('/artist/dashboard', [ArtistController::class, 'dashboard'])->name('artist.dashboard');
    Route::post('/artist/post', [ArtistController::class, 'createPost'])->name('artist.post');
    Route::post('/like', [ArtistController::class, 'like'])->name('like.toggle');
    Route::post('/comment', [ArtistController::class, 'comment'])->name('comment.store');
});

// Upload routes
Route::middleware(['auth'])->group(function () {
    Route::get('/upload', [UploadController::class, 'create'])->name('upload.create');
    Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');
    Route::post('/upload/cloudinary', [UploadController::class, 'uploadToCloudinary'])->name('upload.cloudinary');
});

// Wallet routes
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
    Route::post('/wallet/deposit/callback', [WalletController::class, 'depositCallback'])->name('wallet.deposit.callback');
});

// Payment routes
Route::middleware(['auth'])->group(function () {
    Route::get('/payment/upload-fee', [PaymentController::class, 'uploadFee'])->name('payment.upload-fee');
    Route::post('/payment/initialize', [PaymentController::class, 'initializeFlutterwave'])->name('payment.initialize');
    Route::get('/payment/callback', [PaymentController::class, 'flutterwaveCallback'])->name('payment.callback');
});
Route::get('/payment/verify/{txRef}', [PaymentController::class, 'verifyTransaction'])->name('payment.verify');

// Admin routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/artist/verify/{id}', [AdminController::class, 'verifyArtist'])->name('artist.verify');
});
