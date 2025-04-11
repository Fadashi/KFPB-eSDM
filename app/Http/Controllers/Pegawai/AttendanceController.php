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

        return response()->json([
            'today' => $todayAttendance,
            'monthly' => $monthlyAttendance,
            'statistics' => [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'leave_days' => $leaveDays
            ]
        ]);
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string'
        ]);

        $user = Auth::user();
        $today = now()->format('Y-m-d');
        $currentTime = now()->format('H:i:s');

        // Cek apakah sudah absen hari ini
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'Anda sudah melakukan absensi hari ini'
            ], 400);
        }

        // Simpan foto
        $photoPath = $this->savePhoto($request->photo);

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
            'photo' => $photoPath
        ]);

        return response()->json([
            'message' => 'Absensi berhasil',
            'data' => $attendance
        ]);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string'
        ]);

        $user = Auth::user();
        $today = now()->format('Y-m-d');
        $currentTime = now()->format('H:i:s');

        // Cari absensi hari ini
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'Anda belum melakukan absensi masuk hari ini'
            ], 400);
        }

        if ($attendance->check_out) {
            return response()->json([
                'message' => 'Anda sudah melakukan absensi pulang hari ini'
            ], 400);
        }

        // Simpan foto
        $photoPath = $this->savePhoto($request->photo);

        // Update absensi
        $attendance->update([
            'check_out' => $currentTime,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'photo' => $photoPath
        ]);

        return response()->json([
            'message' => 'Absensi pulang berhasil',
            'data' => $attendance
        ]);
    }

    private function savePhoto($base64Photo)
    {
        $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Photo));
        $filename = 'attendance_' . time() . '.jpg';
        Storage::disk('public')->put('attendance/' . $filename, $image);
        return 'attendance/' . $filename;
    }
} 