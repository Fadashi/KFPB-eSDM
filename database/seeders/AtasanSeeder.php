<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AtasanSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Atasan',
            'email' => 'atasan@kimiafarma.co.id',
            'password' => Hash::make('password123'),
            'role' => 'atasan',
        ]);

        // Tambahkan atasan lainnya jika diperlukan
        User::create([
            'name' => 'Atasan 2',
            'email' => 'atasan2@kimiafarma.co.id',
            'password' => Hash::make('password123'),
            'role' => 'atasan',
        ]);
    }
} 