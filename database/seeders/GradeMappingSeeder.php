<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamSystem\GradeMapping;
use App\Models\ExamSystem\Program;

class GradeMappingSeeder extends Seeder
{
  public function run(): void
  {
    $programs = Program::pluck('id', 'code');
    $grades = [
      ['grade' => 'O', 'min' => 90, 'max' => 100, 'point' => 10],
      ['grade' => 'A+', 'min' => 80, 'max' => 89, 'point' => 9],
      ['grade' => 'A', 'min' => 70, 'max' => 79, 'point' => 8],
      ['grade' => 'B+', 'min' => 60, 'max' => 69, 'point' => 7],
      ['grade' => 'B', 'min' => 50, 'max' => 59, 'point' => 6],
      ['grade' => 'C', 'min' => 40, 'max' => 49, 'point' => 5],
      ['grade' => 'F', 'min' => 0, 'max' => 39, 'point' => 0],
    ];
    $data = [];
    foreach ($programs as $code => $pid) {
      foreach ($grades as $g) {
        $data[] = [
          'program_id' => $pid,
          'grade' => $g['grade'],
          'min_marks' => $g['min'],
          'max_marks' => $g['max'],
          'grade_point' => $g['point'],
          'created_at' => now(),
          'updated_at' => now(),
        ];
      }
    }
    GradeMapping::insert($data);
  }
}
