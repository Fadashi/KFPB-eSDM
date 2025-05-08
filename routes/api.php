<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_middleware'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Route untuk API absensi
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('pegawai')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::get('/attendance/statistics', [AttendanceController::class, 'statistics']);
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    });
});

// API untuk Admin - didefinisikan secara langsung tanpa prefix
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/employees', [EmployeeController::class, 'getEmployees']);
    Route::get('/attendance-report', [AdminAttendanceController::class, 'getAttendanceReport'])->name('api.attendance-report');
});

// Menambahkan route alternatif untuk mengatasi masalah 404
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/attendance-report', [AdminAttendanceController::class, 'getAttendanceReport']);
    Route::get('/admin/employees', [EmployeeController::class, 'getEmployees']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); 