<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');
        
        // Ambil data absensi hari ini
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // Ambil data absensi bulan ini
        $monthlyAttendance = Attendance::where('user_id', $user->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->orderBy('date', 'desc')
            ->get();

        // Hitung statistik
        $totalDays = now()->daysInMonth;
        $presentDays = $monthlyAttendance->where('status', 'Hadir')->count();
        $lateDays = $monthlyAttendance->where('status', 'Terlambat')->count();
        $leaveDays = $monthlyAttendance->where('status', 'Cuti')->count();
        $remainingDays = $totalDays - ($presentDays + $lateDays + $leaveDays);

        return response()->json([
            'today' => $todayAttendance,
            'monthly' => $monthlyAttendance,
            'statistics' => [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'leave_days' => $leaveDays,
                'remaining_days' => $remainingDays
            ]
        ]);
    }

    public function checkIn(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'shift' => 'required|in:non_shift,night_shift,morning_shift,afternoon_shift,pkl,overtime,flexi_time'
            ]);

            $user = Auth::user();
            $today = now()->format('Y-m-d');
            $currentTime = now()->format('H:i:s');

            \Log::info('Check in attempt', [
                'user_id' => $user->id,
                'date' => $today,
                'time' => $currentTime,
                'shift' => $request->shift
            ]);

            // Cek apakah sudah absen hari ini
            $existingAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->first();

            if ($existingAttendance) {
                \Log::warning('Already checked in today', [
                    'user_id' => $user->id,
                    'date' => $today,
                    'check_in' => $existingAttendance->check_in
                ]);
                return response()->json([
                    'message' => 'Anda sudah melakukan absensi hari ini'
                ], 400);
            }

            // Tentukan status (terlambat jika check in setelah 08:00)
            $status = $currentTime > '08:00:00' ? 'Terlambat' : 'Hadir';

            // Buat record absensi
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'check_in' => $currentTime,
                'status' => $status,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'shift' => $request->shift
            ]);

            \Log::info('Check in successful', [
                'user_id' => $user->id,
                'date' => $today,
                'check_in' => $currentTime,
                'status' => $status,
                'shift' => $request->shift
            ]);

            // Ambil data terbaru untuk response
            $updatedData = $this->index()->getData();

            return response()->json([
                'message' => 'Absensi berhasil',
                'data' => $attendance,
                'statistics' => $updatedData->statistics,
                'monthly' => $updatedData->monthly
            ]);
        } catch (\Exception $e) {
            \Log::error('Check in error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat melakukan absensi masuk'
            ], 500);
        }
    }

    public function checkOut(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric'
            ]);

            $user = Auth::user();
            $today = now()->format('Y-m-d');
            $currentTime = now()->format('H:i:s');

            \Log::info('Check out attempt', [
                'user_id' => $user->id,
                'date' => $today,
                'time' => $currentTime
            ]);

            // Cari absensi hari ini
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->first();

            if (!$attendance) {
                \Log::warning('No check-in found for today', [
                    'user_id' => $user->id,
                    'date' => $today
                ]);
                return response()->json([
                    'message' => 'Anda belum melakukan absensi masuk hari ini'
                ], 400);
            }

            if ($attendance->check_out) {
                \Log::warning('Already checked out today', [
                    'user_id' => $user->id,
                    'date' => $today,
                    'check_out' => $attendance->check_out
                ]);
                return response()->json([
                    'message' => 'Anda sudah melakukan absensi pulang hari ini'
                ], 400);
            }

            // Update absensi
            $attendance->update([
                'check_out' => $currentTime,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => 'Sudah Absen'
            ]);

            \Log::info('Check out successful', [
                'user_id' => $user->id,
                'date' => $today,
                'check_out' => $currentTime
            ]);

            // Ambil data terbaru untuk response
            $updatedData = $this->index()->getData();

            return response()->json([
                'message' => 'Absensi pulang berhasil',
                'data' => $attendance->fresh(),
                'statistics' => $updatedData->statistics,
                'monthly' => $updatedData->monthly
            ]);
        } catch (\Exception $e) {
            \Log::error('Check out error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat melakukan absensi pulang'
            ], 500);
        }
    }
} 