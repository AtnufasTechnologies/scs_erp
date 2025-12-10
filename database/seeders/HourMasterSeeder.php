<?php

namespace Database\Seeders;

use App\Models\HourMaster;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HourMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $sl =1;

        for ($i=0; $i < 6; $i++) { 
            HourMaster::insert([
            'title' => $sl++,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        }
    }
}
