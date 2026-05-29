<?php

namespace Database\Seeders;

use App\Models\StudentProgramTypeMaster;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentProgramTypeMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $createdAt = now();
        $updatedAt = now();
        StudentProgramTypeMaster::create([
            'name' => 'UGC',
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);

        StudentProgramTypeMaster::create([
            'name' => 'AICTE',
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
    }
}
