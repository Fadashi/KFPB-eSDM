<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin KFPB',
            'email' => 'admin@kfpb.go.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'nip' => '198501012010011001',
        ]);

        // Atasan
        User::create([
            'name' => 'Atasan KFPB',
            'email' => 'atasan@kfpb.go.id',
            'password' => Hash::make('atasan123'),
            'role' => 'atasan',
            'nip' => '198601012010011002',
        ]);

        // Pegawai
        User::create([
            'name' => 'Pegawai KFPB',
            'email' => 'pegawai@kfpb.go.id',
            'password' => Hash::make('pegawai123'),
            'role' => 'pegawai',
            'nip' => '198701012010011003',
        ]);
    }
} 