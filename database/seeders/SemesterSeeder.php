<?php

namespace Database\Seeders;

use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sl =1;

        for ($i=0; $i < 8; $i++) { 
            Semester::insert([
            'title' => 'Semester '.$sl++,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        }
       
    }
}
