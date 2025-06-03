<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Buat user untuk Faishal Daffa
        $faishalUser = User::create([
            'name' => 'Faishal Daffa',
            'email' => 'faishal.daffa@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
            'nip' => '2024001'
        ]);

        // Buat data karyawan untuk Faishal Daffa
        Employee::create([
            'user_id' => $faishalUser->id,
            'nip' => '2024001',
            'name' => 'Faishal Daffa',
            'gender' => 'L',
            'birth_place' => 'Bandung',
            'birth_date' => '1995-05-15',
            'age' => 28,
            'address' => 'Jl. Contoh No. 123, Bandung',
            'email' => 'faishal.daffa@example.com',
            'mobile_phone' => '081234567890',
            'ktp' => '3271234567890123',
            'employee_status' => 'Aktif',
            'join_date' => '2024-01-01',
            'education' => 'S1',
            'status' => 'Aktif'
        ]);

        // Buat beberapa karyawan dummy lainnya
        $dummyEmployees = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'nip' => '2024002',
                'gender' => 'L',
                'birth_place' => 'Jakarta',
                'birth_date' => '1990-03-20',
                'age' => 33,
                'address' => 'Jl. Merdeka No. 45, Jakarta',
                'mobile_phone' => '081234567891',
                'ktp' => '3171234567890124',
                'employee_status' => 'Aktif',
                'join_date' => '2024-01-15',
                'education' => 'S1',
                'status' => 'Aktif'
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti.aminah@example.com',
                'nip' => '2024003',
                'gender' => 'P',
                'birth_place' => 'Surabaya',
                'birth_date' => '1992-07-10',
                'age' => 31,
                'address' => 'Jl. Sudirman No. 78, Surabaya',
                'mobile_phone' => '081234567892',
                'ktp' => '3571234567890125',
                'employee_status' => 'Aktif',
                'join_date' => '2024-02-01',
                'education' => 'S1',
                'status' => 'Aktif'
            ],
            [
                'name' => 'Ahmad Rizki',
                'email' => 'ahmad.rizki@example.com',
                'nip' => '2024004',
                'gender' => 'L',
                'birth_place' => 'Yogyakarta',
                'birth_date' => '1993-11-25',
                'age' => 30,
                'address' => 'Jl. Malioboro No. 90, Yogyakarta',
                'mobile_phone' => '081234567893',
                'ktp' => '3471234567890126',
                'employee_status' => 'Aktif',
                'join_date' => '2024-02-15',
                'education' => 'S1',
                'status' => 'Aktif'
            ]
        ];

        foreach ($dummyEmployees as $employeeData) {
            // Buat user untuk setiap karyawan
            $user = User::create([
                'name' => $employeeData['name'],
                'email' => $employeeData['email'],
                'password' => Hash::make('password123'),
                'role' => 'pegawai',
                'nip' => $employeeData['nip']
            ]);

            // Buat data karyawan
            Employee::create([
                'user_id' => $user->id,
                ...$employeeData
            ]);
        }
    }
} 