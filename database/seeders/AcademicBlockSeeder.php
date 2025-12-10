<?php

namespace Database\Seeders;

use App\Models\AcademicBlock;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicBlock::insert([
            'campus_id' => 2,
            'title' => 'Nazareth Block',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        AcademicBlock::insert([
            'campus_id' => 2,
            'title' => 'Don Bosco Block',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        AcademicBlock::insert([
            'campus_id' => 2,
            'title' => 'Savio Block',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
