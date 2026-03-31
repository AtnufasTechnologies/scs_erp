<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\Student;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\Subject;
use App\Models\ExamSystem\SeatingAllocation;
use App\Models\ExamSystem\DummyNumber;
use App\Models\ExamSystem\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class AdmitCardService
{
  /**
   * Generate admit card PDF for a student in an exam.
   */
  public function generate($studentId, $examId)
  {
    $student = Student::findOrFail($studentId);
    $exam = Exam::findOrFail($examId);
    $subjects = Registration::where('exam_id', $examId)
      ->where('exam_student_id', $studentId)
      ->with('subject')
      ->get()
      ->pluck('subject');
    $seating = SeatingAllocation::where('exam_student_id', $studentId)
      ->where('exam_schedule_id', $examId)
      ->first();
    $dummy = DummyNumber::where('exam_id', $examId)
      ->where('exam_student_id', $studentId)
      ->first();

    $data = [
      'student' => $student,
      'exam' => $exam,
      'subjects' => $subjects,
      'seating' => $seating,
      'dummy' => $dummy,
    ];

    $pdf = Pdf::loadView('admit_card.pdf', $data);
    return $pdf->download('admit_card_' . $student->id . '_' . $exam->id . '.pdf');
  }
}
