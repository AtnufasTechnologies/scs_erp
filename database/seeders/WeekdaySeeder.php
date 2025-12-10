<?php

namespace Database\Seeders;

use App\Models\Weekday;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WeekdaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
  
            Weekday::insert([
            'title' => 'Monday',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
         Weekday::insert([
            'title' => 'Tueday',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
         Weekday::insert([
            'title' => 'Wednesday',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
          Weekday::insert([
            'title' => 'Thursday',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
          Weekday::insert([
            'title' => 'Friday',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
          Weekday::insert([
            'title' => 'Saturday',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        Weekday::insert([
            'title' => 'Sunday',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        
    }
}
