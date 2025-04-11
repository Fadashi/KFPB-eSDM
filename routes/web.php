<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Pegawai\AttendanceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Debugging session
Route::get('/debug-session', function () {
    return response()->json([
        'user' => Auth::user(),
        'session' => session()->all()
    ]);
})->middleware('auth');

// Dashboard berdasarkan role
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login');
    }

    return redirect()->route(match ($user->role) {
        'admin' => 'admin.dashboard',
        'atasan' => 'atasan.dashboard',
        default => 'pegawai.dashboard',
    });
})->name('dashboard');

// Grup route yang memerlukan autentikasi
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes untuk Admin
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->middleware('checkRole:admin')->name('admin.dashboard');

    Route::get('/admin/employees', function () {
        return Inertia::render('Admin/Employees');
    })->middleware('checkRole:admin')->name('admin.employees');

    Route::get('/admin/settings', function () {
        return Inertia::render('Admin/Settings');
    })->middleware('checkRole:admin')->name('admin.settings');
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

    // API Routes for Attendance
    Route::prefix('api')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    });
});

// Tambahkan route autentikasi dari Laravel Breeze / Jetstream
require __DIR__.'/auth.php';
