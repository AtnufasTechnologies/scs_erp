<?php

namespace Database\Seeders;

use App\Models\AcademicPathwayMaster;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicPathwaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $date = Carbon::now();
        AcademicPathwayMaster::insert([
            'name' => 'Single Major',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        AcademicPathwayMaster::insert([
            'name' => 'Dual Major',
            'created_at' =>  $date,
            'updated_at' =>  $date,
        ]);
    }
}
