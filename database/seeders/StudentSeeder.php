<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamSystem\Student;
use App\Models\ExamSystem\Program;

class StudentSeeder extends Seeder
{
  public function run(): void
  {
    $programs = Program::pluck('id', 'code');
    Student::insert([
      [
        'erp_student_id' => 5001,
        'program_id' => $programs['BSC-NEP'],
        'enrollment_no' => '2023BSC001',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'erp_student_id' => 5002,
        'program_id' => $programs['BCA-AICTE'],
        'enrollment_no' => '2023BCA001',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'erp_student_id' => 5003,
        'program_id' => $programs['MSC-PG'],
        'enrollment_no' => '2023MSC001',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'erp_student_id' => 5004,
        'program_id' => $programs['ITEP'],
        'enrollment_no' => '2023ITEP001',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
      ],
    ]);
  }
}
