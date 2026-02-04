<?php

namespace Database\Seeders;

use App\Models\AdmissionSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdmissionSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdmissionSetting::create([
            'open_date_ug' => null,
            'close_date_ug' => null,
            'instructions_ug' => null,
            'open_date_pg' => null,
            'close_date_pg' => null,
            'instructions_pg' => null,
            'application_fee_ug' => null,
            'application_fee_pg' => null,
        ]);
    }
}
