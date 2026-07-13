<?php

namespace Database\Seeders;

use App\Models\DegreeTrackMaster;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DegreeTrackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */



    public function run(): void
    {
        $date = Carbon::now();

        DegreeTrackMaster::insert([
            'name' => 'Regular',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        DegreeTrackMaster::insert([
            'name' => 'Honours',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        DegreeTrackMaster::insert([
            'name' => 'Honours with Research',
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }
}
