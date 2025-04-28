<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ReferenceSeeder extends Seeder
{
    /**
     * Run all reference seeders.
     */
    public function run(): void
    {
        $this->call([
            RefAgamaSeeder::class,
            RefStatusPegawaiSeeder::class,
            RefJabatanFungsionalSeeder::class,
            RefJabatanStrukturalSeeder::class,
            RefEselonSeeder::class,
            RefJabatanSeeder::class,
            RefBagianSeeder::class,
            RefSubBagianSeeder::class,
        ]);
    }
} 