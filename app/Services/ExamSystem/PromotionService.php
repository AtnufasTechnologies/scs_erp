<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\Promotion;
use App\Models\ExamSystem\Backlog;
use App\Models\ExamSystem\Result;
use App\Models\ExamSystem\StudentCredit;
use App\Models\ExamSystem\ResearchComponent;
use App\Models\ExamSystem\TeachingPractice;
use Illuminate\Support\Facades\DB;

class PromotionService
{
  /**
   * Determine promotion eligibility and update records.
   */
  public function processPromotion($studentId, $fromExamId, $toExamId, $regulationType): array
  {
    $results = Result::where('exam_student_id', $studentId)
      ->where('exam_id', $fromExamId)
      ->first();
    $failedSubjects = $this->countFailedSubjects($studentId, $fromExamId);
    $eligible = false;
    $reason = '';

    switch (strtoupper($regulationType)) {
      case 'NEP':
        $totalCredits = StudentCredit::where('exam_student_id', $studentId)->sum('credits_earned');
        $eligible = $totalCredits >= 40; // Example threshold
        $reason = $eligible ? '' : 'Insufficient credits';
        break;
      case 'AICTE':
        $backlogs = Backlog::where('exam_student_id', $studentId)
          ->where('exam_id', $fromExamId)
          ->where('status', 'pending')
          ->count();
        $eligible = $backlogs <= 4; // Example ATKT rule
        $reason = $eligible ? '' : 'Too many backlogs';
        break;
      case 'PG':
        $eligible = $failedSubjects === 0;
        $reason = $eligible ? '' : 'Strict pass required';
        break;
      case 'ITEP':
        $tp = TeachingPractice::where('exam_student_id', $studentId)
          ->where('exam_id', $fromExamId)
          ->where('status', '!=', 'passed')
          ->exists();
        $eligible = !$tp && $failedSubjects === 0;
        $reason = $eligible ? '' : 'Must pass teaching practice and all subjects';
        break;
      default:
        $eligible = $failedSubjects === 0;
        $reason = $eligible ? '' : 'Standard strict pass';
    }

    // Update promotions table
    if ($eligible) {
      Promotion::updateOrCreate(
        [
          'exam_student_id' => $studentId,
          'from_exam_id' => $fromExamId,
          'to_exam_id' => $toExamId,
        ],
        [
          'promoted_at' => now(),
        ]
      );
    }

    // Update backlog table for failed subjects
    if ($failedSubjects > 0) {
      $this->updateBacklogs($studentId, $fromExamId);
    }

    return [
      'eligible' => $eligible,
      'reason' => $reason,
      'failed_subjects' => $failedSubjects,
    ];
  }

  /**
   * Count failed subjects for a student in an exam.
   */
  public function countFailedSubjects($studentId, $examId): int
  {
    // Assuming Result model has a relation to marks/subjects
    // Here, we simply count results with grade 'F'
    return DB::table('marks')
      ->where('exam_student_id', $studentId)
      ->where('exam_id', $examId)
      ->where('total_marks', '<', 35) // Example fail mark
      ->count();
  }

  /**
   * Update backlog table for failed subjects.
   */
  public function updateBacklogs($studentId, $examId): void
  {
    $failedSubjects = DB::table('marks')
      ->where('exam_student_id', $studentId)
      ->where('exam_id', $examId)
      ->where('total_marks', '<', 35)
      ->pluck('exam_subject_id');
    foreach ($failedSubjects as $subjectId) {
      Backlog::updateOrCreate(
        [
          'exam_student_id' => $studentId,
          'exam_subject_id' => $subjectId,
          'exam_id' => $examId,
        ],
        [
          'status' => 'pending',
        ]
      );
    }
  }
}
