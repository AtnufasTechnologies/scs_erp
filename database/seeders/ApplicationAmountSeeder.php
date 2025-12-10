<?php

namespace Database\Seeders;

use App\Models\AdmissionApplicationFee;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicationAmountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdmissionApplicationFee::insert([
            'amount' => 750.00,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ]);
    }
}
