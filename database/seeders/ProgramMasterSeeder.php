<?php

namespace Database\Seeders;

use App\Models\ProgramMaster;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProgramMaster::insert([
            'title' => 'UG',
            'created_at' => Carbon::now(),
        ]);
        ProgramMaster::insert([
            'title' => 'PG',
            'created_at' => Carbon::now(),
        ]);
    }
}
