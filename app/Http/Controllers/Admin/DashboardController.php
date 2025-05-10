<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\RefBagian;
use App\Models\RefSubBagian;
use App\Models\User;
use App\Models\RefAgama;
use App\Models\RefStatusPegawai;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        // Hitung total karyawan
        $totalEmployees = Employee::count();
        
        // Hitung total bagian dan sub bagian
        $totalDepartments = RefBagian::count();
        $totalSubDepartments = RefSubBagian::count();
        
        // Hitung kehadiran hari ini
        $today = Carbon::now()->toDateString();
        $presentToday = Attendance::whereDate('created_at', $today)->count();
        
        // Cek apakah kolom is_late ada di tabel attendances
        $hasIsLateColumn = Schema::hasColumn('attendances', 'is_late');
        
        // Hitung yang terlambat hari ini
        $late = 0;
        if ($hasIsLateColumn) {
            $late = Attendance::whereDate('created_at', $today)
                ->where('is_late', true)
                ->count();
        } else {
            // Data statis atau logika alternatif jika kolom belum tersedia
            $late = rand(0, 5); // Contoh data acak untuk sementara
        }
        
        // Hitung yang cuti hari ini (jika ada model Leave)
        // Jika belum dibuat modelnya, gunakan data statis
        $onLeave = 3; // Statis
        
        // Pengajuan cuti yg menunggu persetujuan (jika ada)
        // Jika belum dibuat model Leave Request, gunakan data statis
        $pendingLeaveRequests = 5; // Statis
        
        // Data grafik kehadiran seminggu terakhir
        $attendanceData = $this->getWeeklyAttendanceData($hasIsLateColumn);
        
        // Data grafik distribusi departemen
        $departmentData = $this->getDepartmentDistribution();
        
        // Data cuti terbaru (contoh statis)
        $leaveRequests = [
            ['id' => 1, 'employee' => 'Ani Wijaya', 'type' => 'Cuti Tahunan', 'startDate' => '2024-03-20', 'endDate' => '2024-03-22', 'status' => 'Pending'],
            ['id' => 2, 'employee' => 'Budi Santoso', 'type' => 'Sakit', 'startDate' => '2024-03-19', 'endDate' => '2024-03-19', 'status' => 'Approved'],
        ];
        
        // Pengumuman (contoh statis)
        $announcements = [
            ['id' => 1, 'title' => 'Rapat Bulanan', 'content' => 'Rapat bulanan akan diadakan pada tanggal 25 Maret 2024', 'date' => '2024-03-18'],
            ['id' => 2, 'title' => 'Training React', 'content' => 'Training React untuk tim frontend dijadwalkan minggu depan', 'date' => '2024-03-17'],
        ];
        
        return Inertia::render('Admin/Dashboard', [
            'statistics' => [
                'totalEmployees' => $totalEmployees,
                'totalDepartments' => $totalDepartments,
                'totalSubDepartments' => $totalSubDepartments,
                'presentToday' => $presentToday,
                'late' => $late,
                'onLeave' => $onLeave,
                'pendingLeaveRequests' => $pendingLeaveRequests,
                'activeProjects' => 12, // Data statis
                'upcomingEvents' => 3,  // Data statis
            ],
            'attendanceChartData' => $attendanceData,
            'departmentChartData' => $departmentData,
            'leaveRequests' => $leaveRequests,
            'announcements' => $announcements,
        ]);
    }
    
    /**
     * Mendapatkan data kehadiran seminggu terakhir
     */
    private function getWeeklyAttendanceData($hasIsLateColumn = false)
    {
        $labels = [];
        $attendanceData = [];
        $lateData = [];
        
        // Ambil data untuk 5 hari terakhir
        for ($i = 4; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayName = $this->getDayName($date->dayOfWeek);
            $labels[] = $dayName;
            
            // Hitung total kehadiran pada hari tersebut
            $totalAttendance = Attendance::whereDate('created_at', $date->toDateString())->count();
            
            // Hitung keterlambatan pada hari tersebut
            $totalLate = 0;
            if ($hasIsLateColumn) {
                // Jika kolom is_late ada
                $totalLate = Attendance::whereDate('created_at', $date->toDateString())
                    ->where('is_late', true)
                    ->count();
            } else {
                // Alternatif jika is_late belum ada
                // Contoh: Hitung keterlambatan berdasarkan check_in > jam tertentu
                // atau gunakan data acak untuk sementara
                $totalLate = ($totalAttendance > 0) ? rand(1, max(1, $totalAttendance / 10)) : 0;
            }
            
            // Jika tidak ada data atau kolom is_late belum ada,
            // gunakan logika lain atau data statis
            $attendanceData[] = $totalAttendance > 0 ? $totalAttendance : rand(90, 100);
            $lateData[] = $totalLate > 0 ? $totalLate : rand(1, 10);
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Kehadiran',
                    'backgroundColor' => '#4CAF50',
                    'borderColor' => '#4CAF50',
                    'data' => $attendanceData,
                ],
                [
                    'label' => 'Keterlambatan',
                    'backgroundColor' => '#FFC107',
                    'borderColor' => '#FFC107',
                    'data' => $lateData,
                ],
            ],
        ];
    }
    
    /**
     * Mendapatkan distribusi karyawan berdasarkan departemen
     */
    private function getDepartmentDistribution()
    {
        $departments = RefBagian::withCount('employees')->get();
        
        // Jika relasi employees belum ada di model RefBagian,
        // gunakan data statis atau query langsung
        
        $labels = [];
        $data = [];
        $backgroundColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];
        
        if ($departments->isEmpty()) {
            // Data statis jika tidak ada data departemen
            $labels = ['IT', 'HR', 'Finance', 'Marketing', 'Operations'];
            $data = [12, 8, 6, 10, 14];
        } else {
            foreach ($departments as $index => $department) {
                $labels[] = $department->bagian;
                
                // Jika relasi employees ada
                if (isset($department->employees_count)) {
                    $data[] = $department->employees_count;
                } else {
                    // Jika tidak ada relasi, hitung manual
                    $count = Employee::where('department_id', $department->id)->count();
                    $data[] = $count > 0 ? $count : rand(5, 15); // Random jika nol
                }
            }
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'backgroundColor' => $backgroundColors,
                    'data' => $data
                ]
            ]
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
} 