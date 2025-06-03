<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Pegawai\AttendanceController;
use App\Http\Controllers\RefJabatanController;
use App\Http\Controllers\RefBagianController;
use App\Http\Controllers\RefSubBagianController;
use App\Http\Controllers\RefShiftController;
use App\Http\Controllers\RefLiburController;
use App\Http\Controllers\RefAgamaController;
use App\Http\Controllers\RefCutiController;
use App\Http\Controllers\RefStatusPegawaiController;
use App\Http\Controllers\RefJabatanStrukturalController;
use App\Http\Controllers\RefJabatanFungsionalController;
use App\Http\Controllers\RefEselonController;
use App\Http\Controllers\RefBerkasController;
use App\Http\Controllers\RefPayrollController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\Admin\EmployeeImportExportController;
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
    Route::get('/admin/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
        ->middleware('checkRole:admin')
        ->name('admin.dashboard');

    Route::get('/admin/references', function () {
        return Inertia::render('Admin/References');
    })->middleware('checkRole:admin')->name('admin.references');
    
    // API Route untuk Referensi Jabatan
    Route::prefix('api')->middleware('checkRole:admin')->group(function () {
        Route::apiResource('jabatan', RefJabatanController::class);
        Route::apiResource('bagian', RefBagianController::class);
        Route::apiResource('subbagian', RefSubBagianController::class);
        Route::apiResource('shift', RefShiftController::class);
        Route::apiResource('libur', RefLiburController::class);
        Route::apiResource('agama', RefAgamaController::class);
        Route::apiResource('cuti', RefCutiController::class);
        Route::apiResource('status-pegawai', RefStatusPegawaiController::class);
        Route::apiResource('jabatan-struktural', RefJabatanStrukturalController::class);
        Route::apiResource('jabatan-fungsional', RefJabatanFungsionalController::class);
        Route::apiResource('eselon', RefEselonController::class);
        Route::apiResource('berkas', RefBerkasController::class);
        Route::apiResource('payroll', RefPayrollController::class);
    });

    //Karyawan
    Route::get('/admin/employees', [EmployeeController::class, 'index'])->middleware('checkRole:admin')->name('admin.employees');
    Route::get('/admin/employees/create', [EmployeeController::class, 'create'])->middleware('checkRole:admin')->name('admin.employees.create');
    Route::post('/admin/employees/store', [EmployeeController::class, 'store'])->middleware('checkRole:admin')->name('admin.employees.store');
    Route::get('/admin/employees/template', [EmployeeImportExportController::class, 'template'])->name('employees.template');
    Route::get('/admin/employees/export', [EmployeeImportExportController::class, 'export'])->name('employees.export');
    Route::post('/admin/employees/import', [EmployeeImportExportController::class, 'import'])->name('employees.import');
    Route::get('/admin/employees/{id}', [EmployeeController::class, 'show'])->middleware('checkRole:admin')->name('admin.employees.show');
    Route::get('/admin/employees/{id}/edit', [EmployeeController::class, 'edit'])->middleware('checkRole:admin')->name('admin.employees.edit');
    Route::put('/admin/employees/{id}', [EmployeeController::class, 'update'])->middleware('checkRole:admin')->name('admin.employees.update');
    Route::delete('/admin/employees/{id}', [EmployeeController::class, 'destroy'])->middleware('checkRole:admin')->name('admin.employees.destroy');
    
    // API untuk dropdown dinamis
    Route::get('/api/provinces/{id}/cities', [EmployeeController::class, 'getCities'])->name('api.cities');
    Route::get('/api/cities/{id}/districts', [EmployeeController::class, 'getDistricts'])->name('api.districts');
    Route::get('/api/employees', [EmployeeController::class, 'getEmployees'])->name('api.employees');
    Route::get('/api/attendance-report', [App\Http\Controllers\Admin\AttendanceController::class, 'getAttendanceReport'])->name('api.attendance-report');
    Route::get('/api/refresh-sub-departments', [EmployeeController::class, 'refreshSubDepartments'])->middleware('checkRole:admin')->name('api.refresh-sub-departments');

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
    Route::get('/admin/users', [UserController::class, 'index'])
        ->middleware('checkRole:admin')
        ->name('admin.users');
    Route::post('/admin/users', [UserController::class, 'store'])
        ->middleware('checkRole:admin')
        ->name('admin.users.store');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])
        ->middleware('checkRole:admin')
        ->name('admin.users.update');
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])
        ->middleware('checkRole:admin')
        ->name('admin.users.destroy');

    // Audit Trail
    Route::get('/admin/audit-trail', [AuditTrailController::class, 'index'])
        ->middleware('checkRole:admin')
        ->name('admin.audit-trail');

    //Pengaturan
    Route::get('/admin/settings', function () {
        return Inertia::render('Admin/Settings');
    })->middleware('checkRole:admin')->name('admin.settings');

    // Route untuk RefLibur
    Route::get('/holidays', [RefLiburController::class, 'index'])->name('holidays.index');
    Route::post('/holidays', [RefLiburController::class, 'store'])->name('holidays.store');
    Route::put('/holidays/{refLibur}', [RefLiburController::class, 'update'])->name('holidays.update');
    Route::delete('/holidays/{refLibur}', [RefLiburController::class, 'destroy'])->name('holidays.destroy');
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

    // Route untuk approval cuti
    Route::get('/leave-approvals', function () {
        return Inertia::render('Atasan/LeaveApprovals');
    })->name('atasan.leave-approvals');

    // Route untuk approval lembur
    Route::get('/overtime-approvals', function () {
        return Inertia::render('Atasan/OvertimeApprovals');
    })->name('atasan.overtime-approvals');

    // API Routes untuk approval cuti
    Route::prefix('api')->group(function () {
        Route::get('/leave-requests', [LeaveRequestController::class, 'getPendingRequests']);
        Route::put('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
        Route::put('/leave-requests/{id}/reject', [LeaveRequestController::class, 'reject']);

        // API Routes untuk approval lembur
        Route::get('/overtime-requests', [OvertimeRequestController::class, 'getPendingRequests']);
        Route::put('/overtime-requests/{id}/approve', [OvertimeRequestController::class, 'approve']);
        Route::put('/overtime-requests/{id}/reject', [OvertimeRequestController::class, 'reject']);
    });
});

// Routes untuk Pegawai
Route::middleware(['auth', 'checkRole:pegawai'])->prefix('pegawai')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Pegawai/Dashboard');
    })->name('pegawai.dashboard');

    Route::get('/profile', function () {
        return Inertia::render('Pegawai/Profile');
    })->name('pegawai.profile');

    Route::get('/attendance', function () {
        return Inertia::render('Pegawai/Attendance');
    })->name('pegawai.attendance');

    Route::get('/leave-requests', function () {
        return Inertia::render('Pegawai/LeaveRequests');
    })->name('pegawai.leave-requests');

    // Tambahkan route untuk pengajuan lembur
    Route::get('/lembur', function () {
        return Inertia::render('Pegawai/OvertimeRequest');
    })->name('pegawai.lembur');

    // API Routes for Attendance
    Route::prefix('api')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    });

    Route::get('/pegawai/cuti', function() {
        return Inertia::render('Pegawai/LeaveRequest');
    })->name('pegawai.cuti');

    Route::get('/api/overtime-history', [\App\Http\Controllers\OvertimeRequestController::class, 'index']);
    Route::post('/api/overtime-request', [\App\Http\Controllers\OvertimeRequestController::class, 'store']);
    Route::get('/api/overtime-request/{overtimeRequest}', [\App\Http\Controllers\OvertimeRequestController::class, 'show']);
    Route::put('/api/overtime-request/{overtimeRequest}', [\App\Http\Controllers\OvertimeRequestController::class, 'update']);
    Route::delete('/api/overtime-request/{overtimeRequest}', [\App\Http\Controllers\OvertimeRequestController::class, 'destroy']);

    Route::get('/riwayat', function () {
        return Inertia::render('Pegawai/Riwayat');
    })->name('pegawai.riwayat');
});

// Tambahkan route autentikasi dari Laravel Breeze / Jetstream
require __DIR__.'/auth.php';

// Route untuk API absensi
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('pegawai/api')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::get('/attendance/statistics', [AttendanceController::class, 'statistics']);
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
        
        // API Routes for Leave Request
        Route::get('/leave-history', [LeaveRequestController::class, 'index']);
        Route::apiResource('leave-request', LeaveRequestController::class);
    });
});

// Admin Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users');
    Route::get('/attendance', [App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('attendance');
    Route::get('employees/export', [EmployeeImportExportController::class, 'export'])->name('employees.export');
    Route::get('employees/template', [EmployeeImportExportController::class, 'template'])->name('employees.template');
    Route::post('employees/import', [EmployeeImportExportController::class, 'import'])->name('employees.import');
    Route::get('employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
});
