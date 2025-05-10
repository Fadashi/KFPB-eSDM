<?php

namespace Database\Seeders;

use App\Models\RefSubBagian;
use Illuminate\Database\Seeder;

class RefSubBagianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subBagian = [
            [
                'kode' => 'SB001',
                'subbagian' => 'Sub Bagian Umum dan Kepegawaian'
            ],
            [
                'kode' => 'SB002',
                'subbagian' => 'Sub Bagian Keuangan'
            ],
            [
                'kode' => 'SB003',
                'subbagian' => 'Sub Bagian Perencanaan dan Pelaporan'
            ],
            [
                'kode' => 'SB004',
                'subbagian' => 'Seksi Administrasi Kepegawaian'
            ],
            [
                'kode' => 'SB005',
                'subbagian' => 'Seksi Pengembangan Pegawai'
            ],
            [
                'kode' => 'SB006',
                'subbagian' => 'Seksi Infrastruktur IT'
            ],
            [
                'kode' => 'SB007',
                'subbagian' => 'Seksi Pengembangan Aplikasi'
            ],
            [
                'kode' => 'SB008',
                'subbagian' => 'Seksi Perbendaharaan'
            ],
            [
                'kode' => 'SB009',
                'subbagian' => 'Seksi Akuntansi'
            ],
            [
                'kode' => 'SB010',
                'subbagian' => 'Seksi Aset'
            ]
        ];

        foreach ($subBagian as $item) {
            RefSubBagian::create($item);
        }
    }
} 