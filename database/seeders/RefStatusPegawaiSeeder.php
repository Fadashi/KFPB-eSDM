<?php

namespace Database\Seeders;

use App\Models\RefStatusPegawai;
use Illuminate\Database\Seeder;

class RefStatusPegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statusPegawai = [
            'PNS',
            'CPNS',
            'PPPK',
            'Kontrak',
            'Tetap',
            'Percobaan',
            'Magang',
        ];

        foreach ($statusPegawai as $status) {
            RefStatusPegawai::create([
                'status_pegawai' => $status
            ]);
        }
    }
} 