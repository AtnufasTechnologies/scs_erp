<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamSystem\Program;

class ProgramSeeder extends Seeder
{
  public function run(): void
  {
    Program::insert([
      ['name' => 'B.Sc. (NEP)', 'code' => 'BSC-NEP', 'type' => 'UG-NEP', 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'BCA (AICTE)', 'code' => 'BCA-AICTE', 'type' => 'UG-AICTE', 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'M.Sc. (PG)', 'code' => 'MSC-PG', 'type' => 'PG', 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'ITEP (Integrated)', 'code' => 'ITEP', 'type' => 'ITEP', 'created_at' => now(), 'updated_at' => now()],
    ]);
  }
}
