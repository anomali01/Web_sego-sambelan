<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class BuyerSeeder extends Seeder
{
    /**
     * Akun demo pembeli + profil lengkap (bisa langsung ke menu).
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'pembeli@segosambelan.com'],
            [
                'name' => 'Pembeli Demo',
                'password' => 'password',
                'role' => 'buyer',
                'email_verified_at' => now(),
            ]
        );

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => '081234567890',
                'street_address' => 'Jl. Sambal Nusantara No. 88',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'postal_code' => '60241',
            ]
        );
    }
}
