<?php

use App\Http\Controllers\ProfileController;
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
    //Dashboard
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->middleware('checkRole:admin')->name('admin.dashboard');

    Route::get('/admin/references', function () {
        return Inertia::render('Admin/References');
    })->middleware('checkRole:admin')->name('admin.references');

    //Karyawan
    Route::get('/admin/employees', function () {
        return Inertia::render('Admin/Karyawan/Employees');
    })->middleware('checkRole:admin')->name('admin.employees');

    Route::get('/admin/employees/create', function () {
        return Inertia::render('Admin/Karyawan/AddEmployees');
    })->middleware('checkRole:admin')->name('admin.employees.create');

    Route::post('/admin/employees/store', function () {
        // Logic untuk menyimpan data karyawan
        return redirect()->route('admin.employees');
    })->middleware('checkRole:admin')->name('admin.employees.store');

    Route::get('/admin/employees/{id}/edit', function ($id) {
        return Inertia::render('Admin/Karyawan/EditEmployees', [
            'employeeId' => $id
        ]);
    })->middleware('checkRole:admin')->name('admin.employees.edit');

    //Laporan
    Route::get('/admin/report', function () {
        return Inertia::render('Admin/Report');
    })->middleware('checkRole:admin')->name('admin.report');

    //Absensi
    Route::get('/admin/attendance', function () {
        return Inertia::render('Admin/Attendance');
    })->middleware('checkRole:admin')->name('admin.attendance');

    //Pengumuman
    Route::get('/admin/announcement', function () {
        return Inertia::render('Admin/Announcement');
    })->middleware('checkRole:admin')->name('admin.announcement');

    //User Management
    Route::get('/admin/users', function () {
        return Inertia::render('Admin/Users');
    })->middleware('checkRole:admin')->name('admin.users');

    //Audit Trail
    Route::get('/admin/audit-trail', function () {
        return Inertia::render('Admin/Audit-trail');
        })->middleware('checkRole:admin')->name('admin.audit-trail');

    //Pengaturan
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
});

// Tambahkan route autentikasi dari Laravel Breeze / Jetstream
require __DIR__.'/auth.php';
