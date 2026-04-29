<?php

namespace Database\Seeders;

use App\Models\PaperTypeMaster;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaperTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaperTypeMaster::create(['code' => 'T', 'name' => 'Theory']);
        PaperTypeMaster::create(['code' => 'P', 'name' => 'Practical']);
        PaperTypeMaster::create(['code' => 'TP', 'name' => 'Theory/Practical']);
    }
}
