<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\Mark;
use App\Models\ExamSystem\GradeMapping;
use App\Models\ExamSystem\StudentCredit;
use App\Models\ExamSystem\Backlog;
use App\Models\ExamSystem\CondonationApplication;
use App\Models\ExamSystem\ExamAttendance;
use App\Models\ExamSystem\MalpracticeCase;
use Illuminate\Support\Facades\DB;

class ResultCalculationService
{
  /**
   * Calculate final marks for a student in a subject, including condonation/backlog adjustments.
   */
  /**
   * Calculate final marks for a student in a subject, including attendance/malpractice rules.
   * Returns array: ['marks' => float, 'result' => string]
   */
  public function calculateFinalMarks($studentId, $subjectId, $examId)
  {
    // Check attendance
    $attendance = ExamAttendance::where([
      'exam_id' => $examId,
      'student_id' => $studentId,
      'subject_id' => $subjectId,
    ])->first();
    if ($attendance) {
      if ($attendance->status === 'absent') {
        return ['marks' => 0, 'result' => 'Absent'];
      }
      if ($attendance->status === 'malpractice') {
        return ['marks' => 0, 'result' => 'Withheld'];
      }
    }

    // Check for malpractice case (defensive, in case attendance not set)
    $malpractice = MalpracticeCase::where([
      'exam_id' => $examId,
      'student_id' => $studentId,
      'subject_id' => $subjectId,
    ])->whereIn('status', ['pending', 'blocked'])->first();
    if ($malpractice) {
      return ['marks' => 0, 'result' => 'Withheld'];
    }

    $mark = Mark::where('exam_student_id', $studentId)
      ->where('exam_subject_id', $subjectId)
      ->where('exam_id', $examId)
      ->first();
    if (!$mark) return ['marks' => 0, 'result' => 'Absent'];

    // Condonation adjustment
    $condonation = CondonationApplication::where('exam_student_id', $studentId)
      ->where('status', 'approved')
      ->first();
    $finalMarks = $mark->total_marks;
    if ($condonation) {
      $finalMarks += 5; // Example: add 5 marks for condonation
    }

    // Backlog handling (if any special rule)
    $backlog = Backlog::where('exam_student_id', $studentId)
      ->where('exam_subject_id', $subjectId)
      ->where('exam_id', $examId)
      ->where('status', 'approved')
      ->first();
    if ($backlog) {
      $finalMarks = max($finalMarks, 35); // Example: minimum pass marks for backlog
    }

    return ['marks' => $finalMarks, 'result' => 'Normal'];
  }

  /**
   * Calculate grade for a student in a subject using grade mapping.
   */
  public function calculateGrade($programId, $marks): string
  {
    $grade = GradeMapping::where('program_id', $programId)
      ->where('min_marks', '<=', $marks)
      ->where('max_marks', '>=', $marks)
      ->first();
    return $grade ? $grade->grade : 'F';
  }

  /**
   * Calculate SGPA for a student in an exam (semester).
   */
  public function calculateSGPA($studentId, $examId): float
  {
    $credits = StudentCredit::where('exam_student_id', $studentId)->get();
    $totalCredits = 0;
    $weightedPoints = 0;
    foreach ($credits as $credit) {
      $result = $this->calculateFinalMarks($studentId, $credit->exam_subject_id, $examId);
      if (isset($result['result']) && $result['result'] === 'Absent') {
        $gradePoint = 0;
      } elseif (isset($result['result']) && $result['result'] === 'Withheld') {
        $gradePoint = 0; // Or skip, as per policy
      } else {
        $grade = GradeMapping::where('program_id', $credit->subject->program_id)
          ->where('min_marks', '<=', $result['marks'])
          ->where('max_marks', '>=', $result['marks'])
          ->first();
        $gradePoint = $grade ? $grade->grade_point : 0;
      }
      $weightedPoints += $gradePoint * $credit->credits_earned;
      $totalCredits += $credit->credits_earned;
    }
    return $totalCredits > 0 ? round($weightedPoints / $totalCredits, 2) : 0.0;
  }

  /**
   * Calculate CGPA for a student (across all exams).
   */
  public function calculateCGPA($studentId): float
  {
    $credits = StudentCredit::where('exam_student_id', $studentId)->get();
    $totalCredits = 0;
    $weightedPoints = 0;
    foreach ($credits as $credit) {
      $finalMarks = $this->calculateFinalMarks($studentId, $credit->exam_subject_id, $credit->subject->program_id);
      $grade = GradeMapping::where('program_id', $credit->subject->program_id)
        ->where('min_marks', '<=', $finalMarks)
        ->where('max_marks', '>=', $finalMarks)
        ->first();
      $gradePoint = $grade ? $grade->grade_point : 0;
      $weightedPoints += $gradePoint * $credit->credits_earned;
      $totalCredits += $credit->credits_earned;
    }
    return $totalCredits > 0 ? round($weightedPoints / $totalCredits, 2) : 0.0;
  }
}
