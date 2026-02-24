<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminLogController;
use App\Http\Controllers\Admin\ChirpModerationController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;
use App\Http\Controllers\Auth\Register;
use App\Http\Controllers\ChirpController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;       


// ADMIN ROUTES - Protected by EnsureAdmin middleware
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/ban', [UserController::class, 'ban'])->name('users.ban');
    Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Content Moderation
    Route::get('/chirps', [ChirpModerationController::class, 'index'])->name('chirps.index');
    Route::get('/chirps/{chirp}', [ChirpModerationController::class, 'show'])->name('chirps.show');
    Route::delete('/chirps/{chirp}', [ChirpModerationController::class, 'destroy'])->name('chirps.destroy');
    Route::post('/chirps/{chirp}/dismiss', [ChirpModerationController::class, 'dismissReports'])->name('chirps.dismiss');

    // Reports
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [AdminReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/{report}/resolve', [AdminReportController::class, 'resolve'])->name('reports.resolve');

    // Admin Logs (Audit Trail)
    Route::get('/logs', [AdminLogController::class, 'index'])->name('logs.index');
});

// SEARCH ROUTE
Route::get('/search', SearchController::class)->name('search');

// LIKE ROUTES
Route::middleware('auth')->group(function () {
    Route::post('/chirps/{chirp}/like', [LikeController::class, 'toggle'])->name('chirps.like');
    Route::get('/chirps/{chirp}/likes', [LikeController::class, 'show'])->name('chirps.likes.show');
});

// User reporting routes
Route::middleware('auth')->group(function () {
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/my-reports', [ReportController::class, 'myReports'])->name('reports.my');
});


// PROFILE ROUTES
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/profile/{user}/likes', [ProfileController::class, 'likes'])->name('profile.likes');
Route::middleware('auth')->group(function () {
    Route::get('/profile/{user}/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/{user}', [ProfileController::class, 'update'])->name('profile.update');
});

// Chirp Routes
Route::get('/', [ChirpController::class, 'index'])->name('home');
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
