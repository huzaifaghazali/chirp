<?php

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;
use App\Http\Controllers\Auth\Register;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChirpController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;

// SEARCH ROUTE 
Route::get('/search', SearchController::class)->name('search');

// LIKE ROUTES 
Route::middleware('auth')->group(function () {
    Route::post('/chirps/{chirp}/like', [LikeController::class, 'toggle'])->name('chirps.like');
    Route::get('/chirps/{chirp}/likes', [LikeController::class, 'show'])->name('chirps.likes.show');
});

// PROFILE ROUTES
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/profile/{user}/likes', [ProfileController::class, 'likes'])->name('profile.likes');
Route::middleware('auth')->group(function () {
    Route::get('/profile/{user}/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/{user}', [ProfileController::class, 'update'])->name('profile.update');
});

// Chirp Routes
Route::get('/', [ChirpController::class, 'index']);
// Protected routes: only users who are authenticated can perform CRUD
Route::middleware('auth')->group(function () {
    Route::post('/chirps', [ChirpController::class, 'store']);
    Route::get('/chirps/{chirp}/edit', [ChirpController::class, 'edit']);
    Route::put('/chirps/{chirp}', [ChirpController::class, 'update']);
    Route::delete('/chirps/{chirp}', [ChirpController::class, 'destroy']);
});
// Route::resource('chirps', ChirpController::class)
//    ->only(['store', 'edit', 'update', 'destroy']);

// REGISTER ROUTES
Route::view('/register', 'auth.register')->middleware('guest')->name('register');
Route::post('/register', Register::class)->middleware('guest');


// LOGIN ROUTES
Route::view('/login', 'auth.login')->middleware('guest')->name('login');
Route::post('/login', Login::class)->middleware('guest');

// LOGOUT ROUTE
Route::post('/logout', Logout::class)->middleware('auth');
