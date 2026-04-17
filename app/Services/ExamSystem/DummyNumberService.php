<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\DummyNumber;
use Illuminate\Support\Str;

class DummyNumberService
{
  /**
   * Generate and lock unique dummy numbers for students in an exam.
   */
  public function generateDummyNumbers($examId, $studentIds)
  {
    foreach ($studentIds as $studentId) {
      $dummy = DummyNumber::firstOrCreate(
        [
          'exam_id' => $examId,
          'exam_student_id' => $studentId,
        ],
        [
          'dummy_number' => $this->generateUniqueCode($examId),
        ]
      );
      $dummy->locked = true;
      $dummy->save();
    }
  }

  /**
   * Generate a unique random code for dummy number.
   */
  protected function generateUniqueCode($examId)
  {
    do {
      $code = strtoupper(Str::random(8));
    } while (DummyNumber::where('exam_id', $examId)->where('dummy_number', $code)->exists());
    return $code;
  }
}
