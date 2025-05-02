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
                'shift' => 'required|string'
            ]);

            $user = Auth::user();
            $today = now()->setTimezone('Asia/Jakarta');
            $currentTime = $today->format('H:i:s');

            \Log::info('Check in attempt', [
                'user_id' => $user->id,
                'date' => $today->format('Y-m-d'),
                'time' => $currentTime,
                'location' => [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude
                ]
            ]);

            // Cek apakah sudah check in hari ini
            $existingAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $today->format('Y-m-d'))
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check in hari ini'
                ], 400);
            }

            // Tentukan status berdasarkan waktu
            $status = 'Hadir';
            if ($currentTime > '08:00:00') {
                $status = 'Terlambat';
            }

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $today->format('Y-m-d'),
                'check_in' => $currentTime,
                'status' => $status,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'shift' => $request->shift
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check in berhasil',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            \Log::error('Check in error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat check in: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $R = 6371; // Radius bumi dalam kilometer
        $dLat = ($lat2 - $lat1) * M_PI / 180;
        $dLon = ($lon2 - $lon1) * M_PI / 180;
        $a = sin($dLat/2) * sin($dLat/2) +
             cos($lat1 * M_PI / 180) * cos($lat2 * M_PI / 180) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }

    public function checkOut(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric'
            ]);

            $user = Auth::user();
            $today = now()->setTimezone('Asia/Jakarta');
            $currentTime = $today->format('H:i:s');

            \Log::info('Check out attempt', [
                'user_id' => $user->id,
                'date' => $today->format('Y-m-d'),
                'time' => $currentTime,
                'location' => [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude
                ]
            ]);

            // Cari absensi hari ini
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $today->format('Y-m-d'))
                ->first();

            if (!$attendance) {
                \Log::warning('No check-in found for today', [
                    'user_id' => $user->id,
                    'date' => $today->format('Y-m-d')
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan absensi masuk hari ini'
                ], 400);
            }

            if ($attendance->check_out) {
                \Log::warning('Already checked out today', [
                    'user_id' => $user->id,
                    'date' => $today->format('Y-m-d'),
                    'check_out' => $attendance->check_out
                ]);
                return response()->json([
                    'success' => false,
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
                'date' => $today->format('Y-m-d'),
                'check_out' => $currentTime
            ]);

            // Ambil data terbaru
            $updatedData = $this->index()->getData();

            return response()->json([
                'success' => true,
                'message' => 'Absensi pulang berhasil',
                'data' => $attendance,
                'monthly' => $updatedData->monthly,
                'statistics' => $updatedData->statistics
            ]);

        } catch (\Exception $e) {
            \Log::error('Check out error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan absensi pulang: ' . $e->getMessage()
            ], 500);
        }
    }
} 