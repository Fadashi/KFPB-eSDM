<?php

namespace Database\Seeders;

use App\Models\RefAgama;
use Illuminate\Database\Seeder;

class RefAgamaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $daftarAgama = [
            'Islam',
            'Kristen',
            'Katolik',
            'Hindu',
            'Buddha',
            'Khonghucu',
        ];

        foreach ($daftarAgama as $agama) {
            RefAgama::create([
                'agama' => $agama
            ]);
        }
    }
} 