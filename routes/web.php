<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AlertController;
use App\Models\LoginActivity;

// Landing — serves the login page directly
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/calls');
    }
    return view('login');
});

// Explicit login route → displays standard login/register page
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect('/calls');
    }
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Google Socialite Auth Routes
Route::get('/auth/google', [AuthController::class, 'redirect']);
Route::get('/auth/google/callback', [AuthController::class, 'callback']);

// System status API — public (for landing page)
Route::get('/api/system-status', [DashboardController::class, 'systemStatus']);

// Logout — REAL logout with activity tracking
Route::post('/logout', function () {
    $userId = Auth::id();

    // Track logout activity
    if ($userId) {
        LoginActivity::create([
            'user_id'    => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'action'     => 'logout',
            'login_time' => now(),
        ]);
    }

    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Protected routes — require authentication
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/api/dashboard-data', [DashboardController::class, 'apiData']);

    // Call Analysis
    Route::get('/calls', [CallController::class, 'index'])->name('calls.index');
    Route::post('/calls', [CallController::class, 'store'])->name('calls.store');
    Route::get('/calls/{call}', [CallController::class, 'show'])->name('calls.show');

    // Call History with search/filter
    Route::get('/history', [DashboardController::class, 'history']);
    Route::get('/export/csv', [DashboardController::class, 'exportCsv'])->name('export.csv');

    // Alerts — REAL operational alerts
    Route::get('/alerts', [DashboardController::class, 'alerts']);
    Route::post('/alerts/{alert}/status', [AlertController::class, 'updateStatus'])->name('alerts.status');
    Route::post('/alerts/{alert}/escalate', [AlertController::class, 'escalate'])->name('alerts.escalate');
    Route::post('/calls/{call}/alert', [AlertController::class, 'createFromCall'])->name('alerts.create');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');


});
