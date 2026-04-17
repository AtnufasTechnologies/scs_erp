<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\Promotion;
use App\Models\ExamSystem\Backlog;
use App\Models\ExamSystem\Result;
use App\Models\ExamSystem\ResultSubject;
use App\Models\ExamSystem\Student;
use App\Models\ExamSystem\StudentCredit;
use App\Models\ExamSystem\StudentPromotionHistory;
use Illuminate\Support\Facades\DB;

class PromotionService
{
  /**
   * NEP Promotion Model:
   * - All students are ALWAYS promoted to the next semester
   * - No detention — failed subjects become backlogs
   * - Credits are tracked for passed subjects
   * - Backlogs can be reattempted independently
   * - Promotion is independent of result (pass/fail)
   */

  /**
   * Process promotion for all results in an exam session.
   */
  public function processSessionPromotion(int $examSessionId): array
  {
    $results = Result::with(['student', 'examSession', 'resultSubjects'])
      ->where('exam_session_id', $examSessionId)
      ->where('is_published', true)
      ->get();

    $promoted = 0;
    $promotedWithBacklogs = 0;
    $withheld = 0;
    $errors = [];

    foreach ($results as $result) {
      try {
        $outcome = $this->processStudentPromotion($result);
        if ($outcome['status'] === 'withheld') {
          $withheld++;
        } elseif ($outcome['backlogs'] > 0) {
          $promotedWithBacklogs++;
          $promoted++;
        } else {
          $promoted++;
        }
      } catch (\Exception $e) {
        $errors[] = "Student {$result->exam_student_id}: {$e->getMessage()}";
      }
    }

    return [
      'promoted' => $promoted,
      'promoted_with_backlogs' => $promotedWithBacklogs,
      'withheld' => $withheld,
      'errors' => $errors,
    ];
  }

  /**
   * Process promotion for a single student result.
   * NEP: Always promote. Track failed subjects as backlogs. Award credits for passed.
   */
  public function processStudentPromotion(Result $result): array
  {
    $student = $result->student;
    if (!$student) {
      return ['status' => 'error', 'reason' => 'Student not found'];
    }

    $session = $result->examSession;
    $currentSemester = $session->semester ?? $student->current_semester ?? 1;

    // If result is withheld, do not promote
    if ($result->result_status === 'withheld') {
      $this->archiveStudentRecord($result, $student, $currentSemester, 'withheld', null);
      $student->update(['promotion_status' => 'withheld']);
      return ['status' => 'withheld', 'reason' => 'Result withheld', 'backlogs' => 0];
    }

    // Get failed and passed subjects
    $failedSubjects = $this->getFailedSubjects($result);
    $passedSubjects = $this->getPassedSubjects($result);
    $failedCount = $failedSubjects->count();

    // 1. Award credits for passed subjects
    $this->awardCredits($result, $passedSubjects, $currentSemester);

    // 2. Create backlogs for failed subjects (with attempt tracking)
    $this->createBacklogs($result, $failedSubjects, $currentSemester);

    // 3. Clear any previously pending backlogs that are now passed
    $this->clearPassedBacklogs($result, $passedSubjects);

    // 4. Calculate credit totals
    $earnedCredits = (int) StudentCredit::where('exam_student_id', $student->id)
      ->earned()->sum('credits_earned');
    $transferredCredits = (int) StudentCredit::where('exam_student_id', $student->id)
      ->transferred()->whereIn('status', ['active', 'verified'])->sum('credits_earned');
    $totalCredits = $earnedCredits + $transferredCredits;

    // 5. Get pending backlog count
    $pendingBacklogs = Backlog::where('exam_student_id', $student->id)
      ->where('status', 'pending')
      ->count();

    // Backlog subject list for snapshot
    $backlogSubjectIds = $failedSubjects->pluck('erp_subject_id')->toArray();

    // NEP: Always promote to next semester
    $nextSemester = $currentSemester + 1;
    $promotionStatus = $failedCount > 0 ? 'promoted_with_backlogs' : 'promoted';

    // 6. Create promotion record
    $promotion = Promotion::updateOrCreate(
      [
        'exam_student_id' => $student->id,
        'from_exam_id' => $result->exam_id,
        'exam_session_id' => $result->exam_session_id,
      ],
      [
        'to_exam_id' => $result->exam_id,
        'regulation_type' => 'NEP',
        'promotion_status' => $promotionStatus,
        'from_semester' => $currentSemester,
        'to_semester' => $nextSemester,
        'total_credits' => $totalCredits,
        'earned_credits' => $earnedCredits,
        'transferred_credits' => $transferredCredits,
        'pending_backlogs' => $pendingBacklogs,
        'backlog_subjects' => $backlogSubjectIds ?: null,
        'reason' => $failedCount > 0 ? "Promoted with {$failedCount} backlog(s)" : null,
        'promoted_at' => now(),
      ]
    );

    // 7. Archive student record
    $this->archiveStudentRecord($result, $student, $currentSemester, $promotionStatus, $promotion, $earnedCredits, $transferredCredits, $backlogSubjectIds);

    // 8. Update student semester — always move forward
    $student->update([
      'current_semester' => $nextSemester,
      'promotion_status' => $promotionStatus,
    ]);

    return [
      'status' => $promotionStatus,
      'reason' => $failedCount > 0 ? "Promoted with {$failedCount} backlog(s)" : 'Clear promotion',
      'from_semester' => $currentSemester,
      'to_semester' => $nextSemester,
      'earned_credits' => $earnedCredits,
      'transferred_credits' => $transferredCredits,
      'total_credits' => $totalCredits,
      'backlogs' => $pendingBacklogs,
    ];
  }

  /**
   * Get failed subjects from a result.
   */
  protected function getFailedSubjects(Result $result)
  {
    return $result->resultSubjects->filter(function ($subject) {
      return $subject->grade === 'F' || $subject->grade === 'Ab' || $subject->result_status === 'Absent';
    });
  }

  /**
   * Get passed subjects from a result.
   */
  protected function getPassedSubjects(Result $result)
  {
    return $result->resultSubjects->filter(function ($subject) {
      return $subject->result_status === 'Normal' && $subject->grade !== 'F' && $subject->credits > 0;
    });
  }

  /**
   * Award credits for passed subjects (earned type).
   */
  protected function awardCredits(Result $result, $passedSubjects, int $semester): void
  {
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
    }
  }

  /**
   * Create backlog entries for failed subjects with attempt tracking.
   */
  protected function createBacklogs(Result $result, $failedSubjects, int $semester): void
  {
    foreach ($failedSubjects as $subject) {
      // Check if there's already a pending backlog for this subject
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
  }

  /**
   * Clear backlogs for subjects now passed (e.g., backlog re-exam cleared).
   */
  protected function clearPassedBacklogs(Result $result, $passedSubjects): void
  {
    $passedSubjectIds = $passedSubjects->pluck('erp_subject_id');

    if ($passedSubjectIds->isNotEmpty()) {
      Backlog::where('exam_student_id', $result->exam_student_id)
        ->whereIn('exam_subject_id', $passedSubjectIds)
        ->where('status', 'pending')
        ->update([
          'status' => 'cleared',
          'cleared_at' => now(),
          'cleared_exam_session_id' => $result->exam_session_id,
        ]);
    }
  }

  /**
   * Archive the student's current semester record.
   */
  protected function archiveStudentRecord(
    Result $result,
    Student $student,
    int $semester,
    string $promotionStatus,
    ?Promotion $promotion,
    int $earnedCredits = 0,
    int $transferredCredits = 0,
    array $backlogSubjectIds = []
  ): void {
    $subjectsSnapshot = $result->resultSubjects->map(function ($subject) {
      return [
        'erp_subject_id' => $subject->erp_subject_id,
        'subject_code' => $subject->subject_code,
        'subject_name' => $subject->subject_name,
        'fa_marks' => $subject->fa_marks,
        'sa_marks' => $subject->sa_marks,
        'total_marks' => $subject->total_marks,
        'max_marks' => $subject->max_marks,
        'credits' => $subject->credits,
        'grade_point' => $subject->grade_point,
        'grade' => $subject->grade,
        'result_status' => $subject->result_status,
      ];
    })->toArray();

    $pendingBacklogs = Backlog::where('exam_student_id', $student->id)
      ->where('status', 'pending')
      ->count();

    $totalCredits = $earnedCredits + $transferredCredits;

    StudentPromotionHistory::updateOrCreate(
      [
        'exam_student_id' => $student->id,
        'exam_session_id' => $result->exam_session_id,
        'semester' => $semester,
      ],
      [
        'promotion_id' => $promotion?->id,
        'result_status' => $result->result_status,
        'promotion_status' => $promotionStatus,
        'sgpa' => $result->sgpa,
        'percentage' => $result->percentage,
        'total_credits_earned' => $totalCredits,
        'earned_credits' => $earnedCredits,
        'transferred_credits' => $transferredCredits,
        'pending_backlogs' => $pendingBacklogs,
        'backlog_subjects' => $backlogSubjectIds ?: null,
        'subjects_snapshot' => $subjectsSnapshot,
      ]
    );
  }

  /**
   * Get student's complete credit and backlog summary.
   */
  public function getStudentSummary(int $examStudentId): array
  {
    $earnedCredits = (int) StudentCredit::where('exam_student_id', $examStudentId)
      ->earned()->sum('credits_earned');
    $transferredCredits = (int) StudentCredit::where('exam_student_id', $examStudentId)
      ->transferred()->whereIn('status', ['active', 'verified'])->sum('credits_earned');

    $pendingBacklogs = Backlog::where('exam_student_id', $examStudentId)
      ->where('status', 'pending')
      ->with('subject')
      ->get();

    $clearedBacklogs = Backlog::where('exam_student_id', $examStudentId)
      ->where('status', 'cleared')
      ->count();

    return [
      'earned_credits' => $earnedCredits,
      'transferred_credits' => $transferredCredits,
      'total_credits' => $earnedCredits + $transferredCredits,
      'pending_backlogs' => $pendingBacklogs,
      'pending_backlog_count' => $pendingBacklogs->count(),
      'cleared_backlog_count' => $clearedBacklogs,
    ];
  }
}
