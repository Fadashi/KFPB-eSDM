<?php

namespace Database\Seeders;

use App\Models\RefJabatanFungsional;
use Illuminate\Database\Seeder;

class RefJabatanFungsionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatanFungsional = [
            [
                'jabatan_fungsional' => 'Dokter',
                'kelas' => 'III',
                'umur_pensiun' => 60
            ],
            [
                'jabatan_fungsional' => 'Perawat',
                'kelas' => 'II',
                'umur_pensiun' => 58
            ],
            [
                'jabatan_fungsional' => 'Bidan',
                'kelas' => 'II',
                'umur_pensiun' => 58
            ],
            [
                'jabatan_fungsional' => 'Apoteker',
                'kelas' => 'III',
                'umur_pensiun' => 60
            ],
            [
                'jabatan_fungsional' => 'Ahli Teknologi Informasi',
                'kelas' => 'III',
                'umur_pensiun' => 58
            ],
            [
                'jabatan_fungsional' => 'Auditor',
                'kelas' => 'III',
                'umur_pensiun' => 58
            ],
            [
                'jabatan_fungsional' => 'Analis Kepegawaian',
                'kelas' => 'II',
                'umur_pensiun' => 58
            ],
            [
                'jabatan_fungsional' => 'Arsiparis',
                'kelas' => 'II',
                'umur_pensiun' => 58
            ],
            [
                'jabatan_fungsional' => 'Pustakawan',
                'kelas' => 'II',
                'umur_pensiun' => 58
            ]
        ];

        foreach ($jabatanFungsional as $jabatan) {
            RefJabatanFungsional::create($jabatan);
        }
    }
} 