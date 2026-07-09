<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@segosambelan.com'],
            [
                'name' => 'Admin Sego Sambelan',
                'password' => 'password',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}

