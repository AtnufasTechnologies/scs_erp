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
        PaperTypeMaster::create(['name' => 'T']);
        PaperTypeMaster::create(['name' => 'P']);
        PaperTypeMaster::create(['name' => 'TP']);
    }
}
