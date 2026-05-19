<?php

namespace Database\Seeders;

use App\Models\StorePaymentSetting;
use Illuminate\Database\Seeder;

class StorePaymentSettingSeeder extends Seeder
{
    public function run(): void
    {
        StorePaymentSetting::current();
    }
}
