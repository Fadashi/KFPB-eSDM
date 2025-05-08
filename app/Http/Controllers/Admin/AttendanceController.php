<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceController extends Controller
{
    public function index()
    {
        // Hitung total karyawan
        $totalEmployees = Employee::count();
        
        // Hitung kehadiran hari ini
        $today = Carbon::now()->toDateString();
        $presentToday = Attendance::whereDate('date', $today)->count();
        
        // Cek apakah kolom is_late ada di tabel attendances
        $hasIsLateColumn = Schema::hasColumn('attendances', 'is_late');
        
        // Hitung yang terlambat hari ini
        $late = 0;
        if ($hasIsLateColumn) {
            $late = Attendance::whereDate('date', $today)
                ->where('is_late', true)
                ->count();
        } else {
            // Data statis jika kolom belum tersedia
            $late = 2;
        }
        
        // Hitung yang cuti hari ini (jika ada model Leave)
        // Jika belum dibuat modelnya, gunakan data statis
        $onLeave = 3; // Statis
        
        // Data untuk grafik kehadiran minggu ini
        $chartData = $this->getWeeklyAttendanceData($hasIsLateColumn);
        
        // Ambil data kehadiran untuk semua kategori
        $attendanceData = $this->getAttendanceData($today);
        
        return Inertia::render('Admin/Attendance', [
            'statistics' => [
                'totalEmployees' => $totalEmployees,
                'presentToday' => $presentToday,
                'late' => $late,
                'onLeave' => $onLeave,
            ],
            'chartData' => $chartData,
            'attendanceData' => $attendanceData,
        ]);
    }
    
    /**
     * Mendapatkan data kehadiran seminggu terakhir untuk grafik
     */
    private function getWeeklyAttendanceData($hasIsLateColumn = false)
    {
        $labels = [];
        $presentData = [];
        $lateData = [];
        $leaveData = [];
        
        // Ambil data untuk 5 hari terakhir
        for ($i = 4; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayName = $this->getDayName($date->dayOfWeek);
            $labels[] = $dayName;
            
            // Hitung total kehadiran pada hari tersebut
            $totalPresent = Attendance::whereDate('date', $date->toDateString())
                ->where('status', 'present')
                ->count();
            
            // Hitung keterlambatan pada hari tersebut
            $totalLate = 0;
            if ($hasIsLateColumn) {
                $totalLate = Attendance::whereDate('date', $date->toDateString())
                    ->where('is_late', true)
                    ->count();
            } else {
                // Data statis untuk keterlambatan
                $totalLate = rand(2, 7);
            }
            
            // Data statis untuk cuti/izin
            $totalLeave = rand(1, 4);
            
            $presentData[] = $totalPresent ?: rand(40, 47); // Fallback ke data statis jika tidak ada data
            $lateData[] = $totalLate;
            $leaveData[] = $totalLeave;
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Kehadiran',
                    'backgroundColor' => '#4CAF50',
                    'borderColor' => '#4CAF50',
                    'data' => $presentData,
                ],
                [
                    'label' => 'Keterlambatan',
                    'backgroundColor' => '#FFC107',
                    'borderColor' => '#FFC107',
                    'data' => $lateData,
                ],
                [
                    'label' => 'Cuti/Izin',
                    'backgroundColor' => '#2196F3',
                    'borderColor' => '#2196F3',
                    'data' => $leaveData,
                ],
            ],
        ];
    }
    
    /**
     * Mendapatkan data kehadiran karyawan untuk tabel
     */
    private function getAttendanceData($today)
    {
        // Kategori Hadir - Data dari database
        $presentEmployees = Attendance::with(['user.employee'])
            ->whereDate('date', $today)
            ->where('status', 'present')
            ->get()
            ->map(function ($attendance) {
                if ($attendance->user && $attendance->user->employee) {
                    return [
                        'id' => $attendance->user->employee->id,
                        'name' => $attendance->user->employee->name,
                        'position' => $attendance->user->employee->functionalPosition ? 
                            $attendance->user->employee->functionalPosition->jabatan_fungsional : 'Karyawan',
                        'email' => $attendance->user->employee->email,
                        'time' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : '08:00'
                    ];
                }
                return null;
            })
            ->filter()
            ->values()
            ->all();
        
        // Jika data hadir kosong, gunakan data statis
        if (empty($presentEmployees)) {
            $presentEmployees = [
                ['id' => 1, 'name' => 'Budi Santoso', 'position' => 'Frontend Developer', 'email' => 'budi@example.com', 'time' => '08:00'],
                ['id' => 2, 'name' => 'Dedi Kurniawan', 'position' => 'Backend Developer', 'email' => 'dedi@example.com', 'time' => '08:05'],
                ['id' => 3, 'name' => 'Rini Susanti', 'position' => 'UI/UX Designer', 'email' => 'rini@example.com', 'time' => '07:55'],
                ['id' => 8, 'name' => 'Eko Prasetyo', 'position' => 'Frontend Developer', 'email' => 'eko@example.com', 'time' => '08:02'],
                ['id' => 9, 'name' => 'Nina Wati', 'position' => 'Backend Developer', 'email' => 'nina@example.com', 'time' => '08:07'],
                ['id' => 10, 'name' => 'Hadi Sutrisno', 'position' => 'UI/UX Designer', 'email' => 'hadi@example.com', 'time' => '07:58'],
                ['id' => 11, 'name' => 'Maya Sari', 'position' => 'Frontend Developer', 'email' => 'maya@example.com', 'time' => '08:01'],
                ['id' => 12, 'name' => 'Tono Widodo', 'position' => 'Backend Developer', 'email' => 'tono@example.com', 'time' => '08:03'],
            ];
        }
        
        // Kategori Izin - Data statis untuk saat ini
        $leaveEmployees = [
            ['id' => 4, 'name' => 'Ahmad Fauzi', 'position' => 'Project Manager', 'email' => 'ahmad@example.com', 'reason' => 'Cuti Tahunan'],
            ['id' => 5, 'name' => 'Siti Rahayu', 'position' => 'HR Manager', 'email' => 'siti@example.com', 'reason' => 'Izin Pribadi'],
        ];
        
        // Kategori Terlambat - Ambil dari is_late jika ada
        $lateEmployees = [];
        
        if (Schema::hasColumn('attendances', 'is_late')) {
            $lateEmployees = Attendance::with(['user.employee'])
                ->whereDate('date', $today)
                ->where('is_late', true)
                ->get()
                ->map(function ($attendance) {
                    if ($attendance->user && $attendance->user->employee) {
                        return [
                            'id' => $attendance->user->employee->id,
                            'name' => $attendance->user->employee->name,
                            'position' => $attendance->user->employee->functionalPosition ? 
                                $attendance->user->employee->functionalPosition->jabatan_fungsional : 'Karyawan',
                            'email' => $attendance->user->employee->email,
                            'time' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : '09:15'
                        ];
                    }
                    return null;
                })
                ->filter()
                ->values()
                ->all();
        }
        
        // Jika data terlambat kosong, gunakan data statis
        if (empty($lateEmployees)) {
            $lateEmployees = [
                ['id' => 6, 'name' => 'Joko Widodo', 'position' => 'System Analyst', 'email' => 'joko@example.com', 'time' => '09:15'],
                ['id' => 7, 'name' => 'Dewi Lestari', 'position' => 'QA Engineer', 'email' => 'dewi@example.com', 'time' => '09:30'],
            ];
        }
        
        return [
            'hadir' => $presentEmployees,
            'izin' => $leaveEmployees,
            'terlambat' => $lateEmployees,
        ];
    }
    
    /**
     * Mendapatkan nama hari dalam bahasa Indonesia
     */
    private function getDayName($dayOfWeek)
    {
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        
        return $days[$dayOfWeek];
    }

    /**
     * API untuk mendapatkan data laporan absensi
     */
    public function getAttendanceReport(Request $request)
    {
        try {
            // Ambil parameter
            $employeeId = $request->input('employee_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            // Validasi input
            if (!$startDate || !$endDate) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tanggal mulai dan tanggal akhir diperlukan'
                ], 400);
            }
            
            // Ambil data user berdasarkan employee_id
            $userId = null;
            if ($employeeId) {
                $employee = Employee::find($employeeId);
                if ($employee && $employee->user) {
                    $userId = $employee->user->id;
                }
            }
            
            // Query data absensi
            $query = Attendance::with(['user.employee'])
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'asc');
                
            // Filter berdasarkan karyawan jika ada
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            $attendances = $query->get();
            
            // Format data untuk laporan
            $formattedData = $attendances->map(function ($attendance) {
                // Dapatkan data shift
                $shift = $attendance->shift ?? '-';
                
                // Format tanggal absensi
                $date = Carbon::parse($attendance->date)->format('Y-m-d');
                
                // Format jam check in dan check out
                $checkIn = $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i:s') : '-';
                $checkOut = $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i:s') : '-';
                
                // Hitung overtime/lembur
                $overtimeHours = '-';
                if ($attendance->check_in && $attendance->check_out) {
                    // Logika untuk menghitung lembur (jika diperlukan)
                    // Contoh: Lembur dihitung jika check_out lebih dari 8 jam setelah check_in
                    $checkInTime = Carbon::parse($attendance->check_in);
                    $checkOutTime = Carbon::parse($attendance->check_out);
                    $workHours = $checkOutTime->diffInHours($checkInTime);
                    
                    if ($workHours > 8) {
                        $overtimeHours = ($workHours - 8) . ' jam';
                    }
                }
                
                // Status kehadiran
                $status = $attendance->status ?? 'hadir';
                if ($attendance->is_late) {
                    $status = 'terlambat';
                }
                
                return [
                    'date' => $date,
                    'shift' => $shift,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'overtime_hours' => $overtimeHours,
                    'status' => $status,
                    'employee' => $attendance->user && $attendance->user->employee ? [
                        'id' => $attendance->user->employee->id,
                        'name' => $attendance->user->employee->name,
                        'nip' => $attendance->user->employee->nip ?? '-'
                    ] : null
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'attendance' => $formattedData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
} 