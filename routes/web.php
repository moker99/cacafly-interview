<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get(
    '/',
    fn() => auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login')
);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');

// Google OAuth
Route::prefix('auth/google')->name('auth.google.')->group(function () {
    Route::get('/',         [AuthController::class, 'redirectToGoogle'])->name('redirect');
    Route::get('/callback', [AuthController::class, 'handleGoogleCallback'])->name('callback');
});

// Facebook OAuth
Route::prefix('auth/facebook')->name('auth.facebook.')->group(function () {
    Route::get('/',         [AuthController::class, 'redirectToFacebook'])->name('redirect');
    Route::get('/callback', [AuthController::class, 'handleFacebookCallback'])->name('callback');
});

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Q1 – dashboard (profile + liked pages)
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // Q2 – image upload
    Route::get('/upload',  [UploadController::class, 'index'])->name('upload.index');
    Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
