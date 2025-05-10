<?php

namespace Database\Seeders;

use App\Models\RefBagian;
use Illuminate\Database\Seeder;

class RefBagianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bagian = [
            [
                'kode' => 'B001',
                'bagian' => 'Sekretariat'
            ],
            [
                'kode' => 'B002',
                'bagian' => 'Bidang Perencanaan'
            ],
            [
                'kode' => 'B003',
                'bagian' => 'Bidang Keuangan'
            ],
            [
                'kode' => 'B004',
                'bagian' => 'Bidang Kepegawaian'
            ],
            [
                'kode' => 'B005',
                'bagian' => 'Bidang Umum'
            ],
            [
                'kode' => 'B006',
                'bagian' => 'Bidang IT'
            ],
            [
                'kode' => 'B007',
                'bagian' => 'Bidang Hukum'
            ],
            [
                'kode' => 'B008',
                'bagian' => 'Bidang Pengembangan'
            ]
        ];

        foreach ($bagian as $item) {
            RefBagian::create($item);
        }
    }
} 