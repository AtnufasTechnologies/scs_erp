<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamSystem\Subject;
use App\Models\ExamSystem\Program;

class SubjectSeeder extends Seeder
{
  public function run(): void
  {
    $programs = Program::pluck('id', 'code');
    Subject::insert([
      // B.Sc. (NEP)
      ['erp_subject_id' => 1001, 'program_id' => $programs['BSC-NEP'], 'subject_code' => 'PHY101', 'name' => 'Physics I', 'credits' => 4, 'type' => 'Core', 'created_at' => now(), 'updated_at' => now()],
      ['erp_subject_id' => 1002, 'program_id' => $programs['BSC-NEP'], 'subject_code' => 'CHE101', 'name' => 'Chemistry I', 'credits' => 4, 'type' => 'Core', 'created_at' => now(), 'updated_at' => now()],
      ['erp_subject_id' => 1003, 'program_id' => $programs['BSC-NEP'], 'subject_code' => 'MAT101', 'name' => 'Mathematics I', 'credits' => 4, 'type' => 'Core', 'created_at' => now(), 'updated_at' => now()],
      // BCA (AICTE)
      ['erp_subject_id' => 2001, 'program_id' => $programs['BCA-AICTE'], 'subject_code' => 'BCA101', 'name' => 'Programming Fundamentals', 'credits' => 4, 'type' => 'Core', 'created_at' => now(), 'updated_at' => now()],
      ['erp_subject_id' => 2002, 'program_id' => $programs['BCA-AICTE'], 'subject_code' => 'BCA102', 'name' => 'Discrete Mathematics', 'credits' => 4, 'type' => 'Core', 'created_at' => now(), 'updated_at' => now()],
      // M.Sc. (PG)
      ['erp_subject_id' => 3001, 'program_id' => $programs['MSC-PG'], 'subject_code' => 'MSC101', 'name' => 'Advanced Physics', 'credits' => 4, 'type' => 'Core', 'created_at' => now(), 'updated_at' => now()],
      ['erp_subject_id' => 3002, 'program_id' => $programs['MSC-PG'], 'subject_code' => 'MSC102', 'name' => 'Research Methodology', 'credits' => 4, 'type' => 'Core', 'created_at' => now(), 'updated_at' => now()],
      // ITEP
      ['erp_subject_id' => 4001, 'program_id' => $programs['ITEP'], 'subject_code' => 'ITEP101', 'name' => 'Teaching Practice I', 'credits' => 6, 'type' => 'Core', 'created_at' => now(), 'updated_at' => now()],
    ]);
  }
}
