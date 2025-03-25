<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard berdasarkan role
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    $user = $user = Auth::user();


    if (!$user) {
        return redirect()->route('login');
    }

    return Inertia::render(match ($user->role) {
        'admin' => 'Admin/Dashboard',
        'atasan' => 'Atasan/Dashboard',
        default => 'Pegawai/Dashboard',
    });
})->name('dashboard');

// Grup route yang memerlukan autentikasi
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes untuk Admin
Route::middleware(['auth', 'checkRole:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    Route::get('/employees', function () {
        return Inertia::render('Admin/Employees');
    })->name('admin.employees');

    Route::get('/settings', function () {
        return Inertia::render('Admin/Settings');
    })->name('admin.settings');

});

// Routes untuk Atasan
Route::middleware(['auth', 'checkRole:atasan'])->prefix('atasan')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Atasan/Dashboard');
    })->name('atasan.dashboard');

    Route::get('/reports', function () {
        return Inertia::render('Atasan/Reports');
    })->name('atasan.reports');

    Route::get('/approvals', function () {
        return Inertia::render('Atasan/Approvals');
    })->name('atasan.approvals');
});

// Routes untuk Pegawai
Route::middleware(['auth', 'checkRole:pegawai'])->prefix('pegawai')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Pegawai/Dashboard');
    })->name('pegawai.dashboard');

    Route::get('/attendance', function () {
        return Inertia::render('Pegawai/Attendance');
    })->name('pegawai.attendance');

    Route::get('/leave-requests', function () {
        return Inertia::render('Pegawai/LeaveRequests');
    })->name('pegawai.leave-requests');
});

// Tambahkan route autentikasi dari Laravel Breeze / Jetstream
require __DIR__.'/auth.php';
