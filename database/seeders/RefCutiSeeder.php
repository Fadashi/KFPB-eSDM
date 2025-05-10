<?php

namespace Database\Seeders;

use App\Models\RefCuti;
use Illuminate\Database\Seeder;

class RefCutiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisCuti = [
            [
                'nama_cuti' => 'Cuti Tahunan',
                'jatah_cuti' => 12
            ],
            [
                'nama_cuti' => 'Cuti Sakit',
                'jatah_cuti' => 14
            ],
            [
                'nama_cuti' => 'Cuti Hamil',
                'jatah_cuti' => 90
            ],
            [
                'nama_cuti' => 'Cuti Besar',
                'jatah_cuti' => 90
            ],
            [
                'nama_cuti' => 'Cuti Penting',
                'jatah_cuti' => 60
            ],
        ];

        foreach ($jenisCuti as $cuti) {
            RefCuti::create($cuti);
        }
    }
} 