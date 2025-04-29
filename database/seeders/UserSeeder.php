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
        // User dengan role admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // User dengan role atasan
        User::create([
            'name' => 'Kepala Bagian',
            'email' => 'atasan@example.com',
            'password' => Hash::make('password'),
            'role' => 'atasan',
            'email_verified_at' => now(),
        ]);

        // User dengan role pegawai
        User::create([
            'name' => 'Pegawai Satu',
            'email' => 'pegawai1@example.com',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Pegawai Dua',
            'email' => 'pegawai2@example.com',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
            'email_verified_at' => now(),
        ]);

        // Tambah user pegawai
        User::create([
            'name' => '2203645',
            'email' => 'rezky@upi.edu',
            'password' => Hash::make('kfpbb'),
            'role' => 'pegawai'
        ]);

        // Tambah user admin
        User::create([
            'name' => '2204323',
            'email' => 'daffa@upi.edu',
            'password' => Hash::make('admin'),
            'role' => 'admin'
        ]);
    }
} 