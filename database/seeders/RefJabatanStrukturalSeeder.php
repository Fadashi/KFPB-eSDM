<?php

namespace Database\Seeders;

use App\Models\RefJabatanStruktural;
use Illuminate\Database\Seeder;

class RefJabatanStrukturalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatanStruktural = [
            [
                'jabatan_struktural' => 'Kepala Dinas',
                'kelas' => 'I'
            ],
            [
                'jabatan_struktural' => 'Sekretaris Dinas',
                'kelas' => 'I'
            ],
            [
                'jabatan_struktural' => 'Kepala Bidang',
                'kelas' => 'II'
            ],
            [
                'jabatan_struktural' => 'Kepala Sub Bagian',
                'kelas' => 'III'
            ],
            [
                'jabatan_struktural' => 'Kepala Seksi',
                'kelas' => 'III'
            ],
            [
                'jabatan_struktural' => 'Kepala UPT',
                'kelas' => 'II'
            ],
            [
                'jabatan_struktural' => 'Sekretaris UPT',
                'kelas' => 'III'
            ],
            [
                'jabatan_struktural' => 'Kepala Sub Seksi',
                'kelas' => 'IV'
            ]
        ];

        foreach ($jabatanStruktural as $jabatan) {
            RefJabatanStruktural::create($jabatan);
        }
    }
} 