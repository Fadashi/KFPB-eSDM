<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $attendances = Attendance::where('user_id', auth()->id())
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($attendances);
    }

    public function checkIn(Request $request)
    {
        try {
            $today = now();
            $currentTime = $today->format('H:i:s');

            // Cek apakah sudah check in hari ini
            $existingAttendance = Attendance::where('user_id', auth()->id())
                ->whereDate('date', $today->toDateString())
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check in hari ini',
                    'data' => $existingAttendance
                ], 400);
            }

            // Tentukan status berdasarkan waktu
            $status = 'hadir';
            if ($currentTime > '08:00:00') {
                $status = 'terlambat';
            }

            $attendance = Attendance::create([
                'user_id' => auth()->id(),
                'date' => $today->toDateString(),
                'clock_in' => $currentTime,
                'status' => $status,
                'shift' => $request->shift
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check in berhasil',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat check in: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkOut(Request $request)
    {
        try {
            $today = now();
            $currentTime = $today->format('H:i:s');

            // Cek apakah sudah check in hari ini
            $attendance = Attendance::where('user_id', auth()->id())
                ->whereDate('date', $today->toDateString())
                ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan check in hari ini'
                ], 400);
            }

            if ($attendance->clock_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check out hari ini',
                    'data' => $attendance
                ], 400);
            }

            $attendance->update([
                'clock_out' => $currentTime
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check out berhasil',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat check out: ' . $e->getMessage()
            ], 500);
        }
    }

    public function statistics(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $totalDays = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $currentDate = now();
        $daysPassed = $currentDate->month == $month && $currentDate->year == $year 
            ? $currentDate->day 
            : $totalDays;

        $totalAttendances = Attendance::where('user_id', auth()->id())
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->count();

        $statusCounts = Attendance::where('user_id', auth()->id())
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $statistics = [
            'total_days' => $daysPassed,
            'total_attendances' => $totalAttendances,
            'status_counts' => $statusCounts,
            'percentage' => [
                'hadir' => round(($statusCounts['hadir'] ?? 0) / $daysPassed * 100, 2),
                'terlambat' => round(($statusCounts['terlambat'] ?? 0) / $daysPassed * 100, 2),
                'cuti' => round(($statusCounts['cuti'] ?? 0) / $daysPassed * 100, 2),
                'sakit' => round(($statusCounts['sakit'] ?? 0) / $daysPassed * 100, 2),
                'izin' => round(($statusCounts['izin'] ?? 0) / $daysPassed * 100, 2),
            ]
        ];

        return response()->json($statistics);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,terlambat,cuti,sakit,izin',
            'notes' => 'nullable|string'
        ]);

        $attendance = Attendance::create([
            'user_id' => auth()->id(),
            'date' => $request->date,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'status' => $request->status,
            'notes' => $request->notes
        ]);

        return response()->json($attendance, 201);
    }
} 