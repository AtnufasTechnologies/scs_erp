<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\Result;
use App\Models\ExamSystem\ResultSubject;
use App\Models\ExamSystem\Student;
use App\Models\ExamSystem\StudentCredit;
use App\Models\ExamSystem\Backlog;
use App\Models\ExamSystem\GradeMapping;
use App\Models\ExamSystem\ExamSubjectMaster;
use Illuminate\Support\Facades\DB;

class CreditEngineService
{
  /**
   * NEP Credit Engine — triggered ONLY on result publish.
   * Handles: SGPA validation, CGPA computation, credit awarding, backlog processing.
   */

  /**
   * Process all published results for a session — called from publish().
   */
  public function processSession(int $examSessionId): array
  {
    $results = Result::with(['student', 'examSession', 'resultSubjects'])
      ->where('exam_session_id', $examSessionId)
      ->where('is_published', true)
      ->get();

    $processed = 0;
    $errors = [];

    foreach ($results as $result) {
      try {
        $this->processStudentResult($result);
        $processed++;
      } catch (\Exception $e) {
        $errors[] = "Student {$result->exam_student_id}: {$e->getMessage()}";
      }
    }

    return ['processed' => $processed, 'errors' => $errors];
  }

  /**
   * Process a single student result — SGPA, credits, CGPA.
   */
  public function processStudentResult(Result $result): array
  {
    $student = $result->student;
    if (!$student) {
      throw new \RuntimeException('Student not found for result #' . $result->id);
    }

    $session = $result->examSession;
    $semester = $session->semester ?? $student->current_semester ?? 1;

    // 1. Award credits for passed subjects
    $earnedThisSem = $this->awardCreditsForResult($result, $semester);

    // 2. Process backlogs for failed subjects
    $backlogCount = $this->processBacklogs($result, $semester);

    // 3. Clear previously pending backlogs that are now passed
    $this->clearPassedBacklogs($result);

    // 4. Calculate CGPA across all semesters
    $cgpa = $this->calculateCGPA($student->id);

    // 5. Calculate total earned credits
    $totalEarned = (int) StudentCredit::where('exam_student_id', $student->id)
      ->earned()->sum('credits_earned');

    // 6. Update result record with CGPA and earned credits
    $result->update([
      'cgpa' => $cgpa,
      'earned_credits' => $earnedThisSem,
    ]);

    return [
      'sgpa' => $result->sgpa,
      'cgpa' => $cgpa,
      'earned_credits_semester' => $earnedThisSem,
      'total_earned_credits' => $totalEarned,
      'backlogs' => $backlogCount,
    ];
  }

  /**
   * Recalculate credits + CGPA for a student after revaluation.
   * Call after marks/grades have been updated on ResultSubject.
   */
  public function recalculateForRevaluation(Result $result): array
  {
    $student = $result->student;
    $session = $result->examSession;
    $semester = $session->semester ?? $student->current_semester ?? 1;

    // Delete existing credits for this session + student, then re-award
    StudentCredit::where('exam_student_id', $student->id)
      ->where('exam_session_id', $result->exam_session_id)
      ->earned()
      ->delete();

    // Re-award credits based on updated grades
    $earnedThisSem = $this->awardCreditsForResult($result, $semester);

    // Re-process backlogs
    $this->reprocessBacklogsForRevaluation($result, $semester);

    // Recalculate SGPA from result subjects
    $sgpa = $this->calculateSGPA($result);

    // Recalculate CGPA
    $cgpa = $this->calculateCGPA($student->id);

    // Determine new result status
    $resultStatus = $this->determineResultStatus($result);

    // Update result
    $result->update([
      'sgpa' => $sgpa,
      'cgpa' => $cgpa,
      'earned_credits' => $earnedThisSem,
      'result_status' => $resultStatus,
    ]);

    return [
      'sgpa' => $sgpa,
      'cgpa' => $cgpa,
      'earned_credits' => $earnedThisSem,
      'result_status' => $resultStatus,
    ];
  }

  /**
   * Calculate SGPA from result subjects.
   * SGPA = Σ(Grade Point × Credits) / Σ(Credits)
   */
  public function calculateSGPA(Result $result): float
  {
    $subjects = $result->resultSubjects;

    $totalWeightedPoints = 0;
    $totalCredits = 0;

    foreach ($subjects as $subject) {
      if ($subject->result_status !== 'Withheld' && $subject->credits > 0) {
        $totalWeightedPoints += ($subject->grade_point * $subject->credits);
        $totalCredits += $subject->credits;
      }
    }

    return $totalCredits > 0 ? round($totalWeightedPoints / $totalCredits, 2) : 0.0;
  }

  /**
   * Calculate CGPA across all semesters using StudentCredit records.
   * CGPA = Σ(Grade Point × Credits) / Σ(Credits) across ALL earned credits.
   */
  public function calculateCGPA(int $examStudentId): float
  {
    $credits = StudentCredit::where('exam_student_id', $examStudentId)
      ->earned()
      ->whereIn('status', ['active', 'verified'])
      ->get();

    $totalWeightedPoints = 0;
    $totalCredits = 0;

    foreach ($credits as $credit) {
      if ($credit->credits_earned > 0 && $credit->grade_point !== null) {
        $totalWeightedPoints += ($credit->grade_point * $credit->credits_earned);
        $totalCredits += $credit->credits_earned;
      }
    }

    return $totalCredits > 0 ? round($totalWeightedPoints / $totalCredits, 2) : 0.0;
  }

  /**
   * Award credits for passed subjects in a result.
   * Returns total credits earned this semester.
   */
  protected function awardCreditsForResult(Result $result, int $semester): int
  {
    $passedSubjects = $result->resultSubjects->filter(function ($subject) {
      return $subject->result_status === 'Normal' && $subject->grade !== 'F' && $subject->credits > 0;
    });

    $totalEarned = 0;

    foreach ($passedSubjects as $subject) {
      StudentCredit::updateOrCreate(
        [
          'exam_student_id' => $result->exam_student_id,
          'exam_subject_id' => $subject->erp_subject_id,
          'credit_type' => 'earned',
        ],
        [
          'exam_session_id' => $result->exam_session_id,
          'credits_earned' => $subject->credits,
          'semester' => $semester,
          'grade' => $subject->grade,
          'grade_point' => $subject->grade_point,
          'status' => 'active',
        ]
      );

      $totalEarned += $subject->credits;
    }

    return $totalEarned;
  }

  /**
   * Process backlogs for failed subjects.
   */
  protected function processBacklogs(Result $result, int $semester): int
  {
    $failedSubjects = $result->resultSubjects->filter(function ($subject) {
      return $subject->grade === 'F' || $subject->grade === 'Ab' || $subject->result_status === 'Absent';
    });

    foreach ($failedSubjects as $subject) {
      $existing = Backlog::where('exam_student_id', $result->exam_student_id)
        ->where('exam_subject_id', $subject->erp_subject_id)
        ->orderBy('attempt_number', 'desc')
        ->first();

      $attemptNumber = $existing ? $existing->attempt_number + 1 : 1;

      Backlog::updateOrCreate(
        [
          'exam_student_id' => $result->exam_student_id,
          'exam_subject_id' => $subject->erp_subject_id,
          'exam_id' => $result->exam_id,
        ],
        [
          'exam_session_id' => $result->exam_session_id,
          'semester' => $semester,
          'credits' => $subject->credits,
          'status' => 'pending',
          'attempt_number' => $attemptNumber,
          'previous_marks' => $subject->total_marks,
          'previous_grade' => $subject->grade,
        ]
      );
    }

    return $failedSubjects->count();
  }

  /**
   * Clear previously pending backlogs that student has now passed.
   */
  protected function clearPassedBacklogs(Result $result): void
  {
    $passedSubjectIds = $result->resultSubjects
      ->filter(fn($s) => $s->result_status === 'Normal' && $s->grade !== 'F')
      ->pluck('erp_subject_id');

    if ($passedSubjectIds->isNotEmpty()) {
      Backlog::where('exam_student_id', $result->exam_student_id)
        ->whereIn('exam_subject_id', $passedSubjectIds)
        ->where('status', 'pending')
        ->update([
          'status' => 'cleared',
          'cleared_exam_session_id' => $result->exam_session_id,
          'cleared_marks' => null,
          'cleared_grade' => null,
        ]);

      // Update cleared marks/grade from the result subjects
      foreach ($passedSubjectIds as $subjectId) {
        $rs = $result->resultSubjects->firstWhere('erp_subject_id', $subjectId);
        if ($rs) {
          Backlog::where('exam_student_id', $result->exam_student_id)
            ->where('exam_subject_id', $subjectId)
            ->where('status', 'cleared')
            ->where('cleared_exam_session_id', $result->exam_session_id)
            ->update([
              'cleared_marks' => $rs->total_marks,
              'cleared_grade' => $rs->grade,
            ]);
        }
      }
    }
  }

  /**
   * Re-process backlogs after revaluation — a subject that was F may now pass, or vice versa.
   */
  protected function reprocessBacklogsForRevaluation(Result $result, int $semester): void
  {
    foreach ($result->resultSubjects as $subject) {
      $isPassed = $subject->result_status === 'Normal' && $subject->grade !== 'F';

      $backlog = Backlog::where('exam_student_id', $result->exam_student_id)
        ->where('exam_subject_id', $subject->erp_subject_id)
        ->where('exam_session_id', $result->exam_session_id)
        ->first();

      if ($isPassed && $backlog) {
        // Subject now passed — clear the backlog
        $backlog->update([
          'status' => 'cleared',
          'cleared_exam_session_id' => $result->exam_session_id,
          'cleared_marks' => $subject->total_marks,
          'cleared_grade' => $subject->grade,
        ]);
      } elseif (!$isPassed && !$backlog && $subject->result_status !== 'Withheld') {
        // Subject now failed — create backlog
        $existing = Backlog::where('exam_student_id', $result->exam_student_id)
          ->where('exam_subject_id', $subject->erp_subject_id)
          ->orderBy('attempt_number', 'desc')
          ->first();

        Backlog::create([
          'exam_student_id' => $result->exam_student_id,
          'exam_subject_id' => $subject->erp_subject_id,
          'exam_id' => $result->exam_id,
          'exam_session_id' => $result->exam_session_id,
          'semester' => $semester,
          'credits' => $subject->credits,
          'status' => 'pending',
          'attempt_number' => $existing ? $existing->attempt_number + 1 : 1,
          'previous_marks' => $subject->total_marks,
          'previous_grade' => $subject->grade,
        ]);
      } elseif (!$isPassed && $backlog && $backlog->status === 'cleared') {
        // Was cleared but now failed again after reval — revert to pending
        $backlog->update([
          'status' => 'pending',
          'cleared_exam_session_id' => null,
          'cleared_marks' => null,
          'cleared_grade' => null,
          'previous_marks' => $subject->total_marks,
          'previous_grade' => $subject->grade,
        ]);
      }
    }
  }

  /**
   * Determine result status from subjects.
   */
  protected function determineResultStatus(Result $result): string
  {
    $hasWithheld = $result->resultSubjects->contains('result_status', 'Withheld');
    $allPassed = !$result->resultSubjects->contains(function ($s) {
      return $s->grade === 'F' || $s->grade === 'Ab' || $s->result_status === 'Absent';
    });

    if ($hasWithheld) return 'withheld';
    if (!$allPassed) return 'fail';
    return 'pass';
  }

  /**
   * Revert all credit engine effects for a session (called from unpublish).
   */
  public function revertSession(int $examSessionId): void
  {
    // Delete credits awarded for this session
    StudentCredit::where('exam_session_id', $examSessionId)->delete();

    // Delete backlogs created for this session
    Backlog::where('exam_session_id', $examSessionId)->delete();

    // Revert cleared backlogs that were cleared in this session
    Backlog::where('cleared_exam_session_id', $examSessionId)
      ->where('status', 'cleared')
      ->update([
        'status' => 'pending',
        'cleared_exam_session_id' => null,
        'cleared_marks' => null,
        'cleared_grade' => null,
      ]);

    // Reset CGPA and earned_credits on results for this session
    Result::where('exam_session_id', $examSessionId)
      ->update([
        'cgpa' => null,
        'earned_credits' => null,
      ]);
  }

  /**
   * Get credit summary for a student.
   */
  public function getStudentCreditSummary(int $examStudentId): array
  {
    $earnedCredits = (int) StudentCredit::where('exam_student_id', $examStudentId)
      ->earned()->sum('credits_earned');
    $transferredCredits = (int) StudentCredit::where('exam_student_id', $examStudentId)
      ->transferred()->whereIn('status', ['active', 'verified'])->sum('credits_earned');

    $cgpa = $this->calculateCGPA($examStudentId);

    $pendingBacklogs = Backlog::where('exam_student_id', $examStudentId)
      ->where('status', 'pending')->count();
    $clearedBacklogs = Backlog::where('exam_student_id', $examStudentId)
      ->where('status', 'cleared')->count();

    return [
      'earned_credits' => $earnedCredits,
      'transferred_credits' => $transferredCredits,
      'total_credits' => $earnedCredits + $transferredCredits,
      'cgpa' => $cgpa,
      'pending_backlogs' => $pendingBacklogs,
      'cleared_backlogs' => $clearedBacklogs,
    ];
  }
}
