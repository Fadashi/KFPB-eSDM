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

    public function statistics(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $totalDays = Carbon::createFromDate($year, $month, 1)->daysInMonth;
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
            'total_days' => $totalDays,
            'total_attendances' => $totalAttendances,
            'status_counts' => $statusCounts,
            'percentage' => [
                'hadir' => round(($statusCounts['hadir'] ?? 0) / $totalDays * 100, 2),
                'terlambat' => round(($statusCounts['terlambat'] ?? 0) / $totalDays * 100, 2),
                'cuti' => round(($statusCounts['cuti'] ?? 0) / $totalDays * 100, 2),
                'sakit' => round(($statusCounts['sakit'] ?? 0) / $totalDays * 100, 2),
                'izin' => round(($statusCounts['izin'] ?? 0) / $totalDays * 100, 2),
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