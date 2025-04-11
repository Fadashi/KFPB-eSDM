<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class HashPasswords extends Command
{
    protected $signature = 'passwords:hash';
    protected $description = 'Hash passwords that are not yet hashed';

    public function handle()
    {
        $users = User::all();
        $count = 0;

        foreach ($users as $user) {
            // Cek apakah password sudah ter-hash
            if (!Hash::needsRehash($user->password)) {
                continue;
            }

            // Hash password yang belum ter-hash
            $user->password = Hash::make($user->password);
            $user->save();
            $count++;
        }

        $this->info("Successfully hashed {$count} passwords.");
    }
} 