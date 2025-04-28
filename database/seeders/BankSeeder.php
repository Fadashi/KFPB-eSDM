<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            ['code' => 'BCA', 'name' => 'Bank Central Asia'],
            ['code' => 'BNI', 'name' => 'Bank Negara Indonesia'],
            ['code' => 'BRI', 'name' => 'Bank Rakyat Indonesia'],
            ['code' => 'MANDIRI', 'name' => 'Bank Mandiri'],
            ['code' => 'BTN', 'name' => 'Bank Tabungan Negara'],
            ['code' => 'CIMB', 'name' => 'CIMB Niaga'],
            ['code' => 'BSI', 'name' => 'Bank Syariah Indonesia'],
            ['code' => 'PERMATA', 'name' => 'Bank Permata'],
            ['code' => 'MEGA', 'name' => 'Bank Mega'],
            ['code' => 'DANAMON', 'name' => 'Bank Danamon'],
            ['code' => 'OCBC', 'name' => 'OCBC NISP'],
            ['code' => 'PANIN', 'name' => 'Panin Bank'],
            ['code' => 'MAYBANK', 'name' => 'Maybank Indonesia'],
            ['code' => 'CITIBANK', 'name' => 'Citibank'],
            ['code' => 'UOB', 'name' => 'Bank UOB Indonesia'],
        ];

        foreach ($banks as $bank) {
            Bank::firstOrCreate(
                ['code' => $bank['code']],
                ['name' => $bank['name']]
            );
        }
    }
} 