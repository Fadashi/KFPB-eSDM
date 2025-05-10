<?php

namespace Database\Seeders;

use App\Models\RefJabatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah tabel kosong
        $count = DB::table('ref_jabatan')->count();
        
        // Jika tabel tidak kosong, hapus data lama
        if ($count > 0) {
            $this->command->info('Tabel ref_jabatan sudah berisi data, mencoba menambahkan data baru...');
        }

        $jabatan = [
            [
                'kode' => 'J001',
                'jabatan' => 'Staff Administrasi',
                'gaji_pokok' => 3500000.00,
                'tunjangan' => 500000.00,
                'masa' => 1.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode' => 'J002',
                'jabatan' => 'Staff IT',
                'gaji_pokok' => 4500000.00,
                'tunjangan' => 800000.00,
                'masa' => 1.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode' => 'J003',
                'jabatan' => 'Staff Keuangan',
                'gaji_pokok' => 4000000.00,
                'tunjangan' => 700000.00,
                'masa' => 1.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode' => 'J004',
                'jabatan' => 'Staff HR',
                'gaji_pokok' => 4000000.00,
                'tunjangan' => 700000.00,
                'masa' => 1.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode' => 'J005',
                'jabatan' => 'Supervisor Administrasi',
                'gaji_pokok' => 5500000.00,
                'tunjangan' => 1000000.00,
                'masa' => 3.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode' => 'J006',
                'jabatan' => 'Supervisor IT',
                'gaji_pokok' => 6500000.00,
                'tunjangan' => 1200000.00,
                'masa' => 3.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode' => 'J007',
                'jabatan' => 'Supervisor Keuangan',
                'gaji_pokok' => 6000000.00,
                'tunjangan' => 1100000.00,
                'masa' => 3.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode' => 'J008',
                'jabatan' => 'Supervisor HR',
                'gaji_pokok' => 6000000.00,
                'tunjangan' => 1100000.00,
                'masa' => 3.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode' => 'J009',
                'jabatan' => 'Manager Administrasi',
                'gaji_pokok' => 8000000.00,
                'tunjangan' => 2000000.00,
                'masa' => 5.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode' => 'J010',
                'jabatan' => 'Manager IT',
                'gaji_pokok' => 9000000.00,
                'tunjangan' => 2500000.00,
                'masa' => 5.00,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Gunakan insertOrIgnore untuk melewati jika ada duplikasi kode
        DB::table('ref_jabatan')->insertOrIgnore($jabatan);
        
        $this->command->info('Seeder ref_jabatan selesai dijalankan.');
    }
} 