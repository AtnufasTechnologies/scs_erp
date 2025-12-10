<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\MainProgram;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sonada_id =  Campus::insertGetId([
            'slug' => 'sonada',
            'name' => 'Sonada',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ]);

        $siliguri_id = Campus::insertGetId([
            'slug' => 'siliguri-campus',
            'name' => 'Siliguri Campus',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ]);

        MainProgram::insert([
            'campus_Id' => $siliguri_id,
            'name' => 'UG',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        MainProgram::insert([
            'campus_Id' => $siliguri_id,
            'name' => 'PG',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        MainProgram::insert([
            'campus_Id' => $sonada_id,
            'name' => 'UG',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
