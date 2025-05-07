<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UpdateUserStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update semua user yang ada untuk memiliki status Active
        User::whereNull('status')->update(['status' => 'Active']);
        
        // Tampilkan pesan informasi
        $this->command->info('Semua user telah diupdate dengan status Active.');
    }
}
