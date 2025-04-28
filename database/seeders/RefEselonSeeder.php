<?php

namespace Database\Seeders;

use App\Models\RefEselon;
use Illuminate\Database\Seeder;

class RefEselonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eselons = [
            [
                'eselon' => 'I.a',
                'uraian' => 'Jabatan Pimpinan Tinggi Utama'
            ],
            [
                'eselon' => 'I.b',
                'uraian' => 'Jabatan Pimpinan Tinggi Madya'
            ],
            [
                'eselon' => 'II.a',
                'uraian' => 'Jabatan Pimpinan Tinggi Pratama'
            ],
            [
                'eselon' => 'II.b',
                'uraian' => 'Jabatan Pimpinan Tinggi Pratama'
            ],
            [
                'eselon' => 'III.a',
                'uraian' => 'Jabatan Administrator'
            ],
            [
                'eselon' => 'III.b',
                'uraian' => 'Jabatan Administrator'
            ],
            [
                'eselon' => 'IV.a',
                'uraian' => 'Jabatan Pengawas'
            ],
            [
                'eselon' => 'IV.b',
                'uraian' => 'Jabatan Pengawas'
            ],
            [
                'eselon' => 'V.a',
                'uraian' => 'Jabatan Pelaksana'
            ],
            [
                'eselon' => 'V.b',
                'uraian' => 'Jabatan Pelaksana'
            ],
            [
                'eselon' => 'Non Eselon',
                'uraian' => 'Jabatan Fungsional/Pelaksana'
            ]
        ];

        foreach ($eselons as $eselon) {
            RefEselon::create($eselon);
        }
    }
} 