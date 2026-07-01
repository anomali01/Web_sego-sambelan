<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            [
                'name' => 'Driver Budi',
                'email' => 'budi@driver.com',
            ],
            [
                'name' => 'Driver Andi',
                'email' => 'andi@driver.com',
            ],
            [
                'name' => 'Driver Cipto',
                'email' => 'cipto@driver.com',
            ]
        ];

        foreach ($drivers as $driver) {
            User::updateOrCreate(
                ['email' => $driver['email']],
                [
                    'name' => $driver['name'],
                    'password' => Hash::make('password'),
                    'role' => 'driver',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
