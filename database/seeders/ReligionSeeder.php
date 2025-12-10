<?php

namespace Database\Seeders;

use App\Models\ReligionMaster;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReligionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ReligionMaster::insert([
            'name' => 'Hinduism',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        ReligionMaster::insert([
            'name' => 'Buddhism',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        ReligionMaster::insert([
            'name' => 'Islam',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        ReligionMaster::insert([
            'name' => 'Christianity-Catholic',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        ReligionMaster::insert([
            'name' => 'Christianity-Other',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        ReligionMaster::insert([
            'name' => 'Sikhism',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        ReligionMaster::insert([
            'name' => 'Jainism',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        ReligionMaster::insert([
            'name' => 'Judaism',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        ReligionMaster::insert([
            'name' => 'Other',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
