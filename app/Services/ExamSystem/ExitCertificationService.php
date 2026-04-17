<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\ExitCertification;
use App\Models\ExamSystem\Result;
use App\Models\ExamSystem\Student;
use App\Models\ExamSystem\StudentCredit;
use App\Models\ExamSystem\StudentPromotionHistory;
use Illuminate\Support\Facades\DB;

class ExitCertificationService
{
  /**
   * Check a student's eligibility for all exit levels.
   */
  public function checkEligibility(int $examStudentId): array
  {
    $student = Student::with('credits')->findOrFail($examStudentId);
    $totalCredits = $student->credits->sum('credits_earned');
    $semestersCompleted = $this->getSemestersCompleted($examStudentId);
    $cgpa = $this->calculateCGPA($examStudentId);
    $creditSummary = $this->getCreditSummary($examStudentId);

    $eligibility = [];

    foreach (ExitCertification::LEVELS as $level => $config) {
      $creditsOk = $totalCredits >= $config['min_credits'];
      $semestersOk = $semestersCompleted >= $config['min_semesters'];
      $alreadyIssued = ExitCertification::where('exam_student_id', $examStudentId)
        ->where('exit_level', $level)
        ->whereIn('status', ['approved', 'issued'])
        ->exists();

      $eligibility[$level] = [
        'label' => $config['label'],
        'eligible' => $creditsOk && $semestersOk && !$alreadyIssued,
        'credits_required' => $config['min_credits'],
        'credits_earned' => $totalCredits,
        'credits_ok' => $creditsOk,
        'semesters_required' => $config['min_semesters'],
        'semesters_completed' => $semestersCompleted,
        'semesters_ok' => $semestersOk,
        'already_issued' => $alreadyIssued,
      ];
    }

    return [
      'student' => $student,
      'total_credits' => $totalCredits,
      'semesters_completed' => $semestersCompleted,
      'cgpa' => $cgpa,
      'credit_summary' => $creditSummary,
      'eligibility' => $eligibility,
    ];
  }

  /**
   * Determine the highest exit level a student qualifies for.
   */
  public function getHighestEligibleLevel(int $examStudentId): ?string
  {
    $check = $this->checkEligibility($examStudentId);
    $highest = null;

    foreach (array_reverse(array_keys(ExitCertification::LEVELS)) as $level) {
      if ($check['eligibility'][$level]['eligible']) {
        $highest = $level;
        break;
      }
    }

    return $highest;
  }

  /**
   * Issue an exit certification for a student at a given level.
   */
  public function issueCertification(int $examStudentId, string $level, ?string $remarks = null): ExitCertification
  {
    $check = $this->checkEligibility($examStudentId);

    if (!isset($check['eligibility'][$level])) {
      throw new \InvalidArgumentException("Invalid exit level: {$level}");
    }

    if (!$check['eligibility'][$level]['eligible']) {
      $reasons = [];
      if (!$check['eligibility'][$level]['credits_ok']) {
        $reasons[] = "needs {$check['eligibility'][$level]['credits_required']} credits, has {$check['eligibility'][$level]['credits_earned']}";
      }
      if (!$check['eligibility'][$level]['semesters_ok']) {
        $reasons[] = "needs {$check['eligibility'][$level]['semesters_required']} semesters, completed {$check['eligibility'][$level]['semesters_completed']}";
      }
      if ($check['eligibility'][$level]['already_issued']) {
        $reasons[] = "already issued at this level";
      }
      throw new \RuntimeException("Student not eligible for {$level}: " . implode('; ', $reasons));
    }

    $config = ExitCertification::LEVELS[$level];

    return ExitCertification::create([
      'exam_student_id' => $examStudentId,
      'program_id' => $check['student']->program_id,
      'exit_level' => $level,
      'certificate_no' => ExitCertification::generateCertificateNo($level),
      'total_credits_earned' => $check['total_credits'],
      'credits_required' => $config['min_credits'],
      'cgpa' => $check['cgpa'],
      'semesters_completed' => $check['semesters_completed'],
      'status' => 'pending',
      'credit_summary' => $check['credit_summary'],
      'remarks' => $remarks,
    ]);
  }

  /**
   * Count semesters with published results.
   */
  protected function getSemestersCompleted(int $examStudentId): int
  {
    return Result::where('exam_student_id', $examStudentId)
      ->where('is_published', true)
      ->whereNotNull('exam_session_id')
      ->distinct('exam_session_id')
      ->count('exam_session_id');
  }

  /**
   * Calculate cumulative GPA from all published results.
   */
  protected function calculateCGPA(int $examStudentId): float
  {
    $results = Result::where('exam_student_id', $examStudentId)
      ->where('is_published', true)
      ->whereNotNull('sgpa')
      ->get();

    if ($results->isEmpty()) {
      return 0;
    }

    return round($results->avg('sgpa'), 2);
  }

  /**
   * Get semester-wise credit breakdown.
   */
  protected function getCreditSummary(int $examStudentId): array
  {
    $histories = StudentPromotionHistory::where('exam_student_id', $examStudentId)
      ->orderBy('semester')
      ->get();

    if ($histories->isEmpty()) {
      $results = Result::with('examSession')
        ->where('exam_student_id', $examStudentId)
        ->where('is_published', true)
        ->get();

      $summary = [];
      foreach ($results as $result) {
        $sem = $result->examSession->semester ?? '?';
        $credits = $result->resultSubjects()
          ->where('result_status', 'Normal')
          ->where('grade', '!=', 'F')
          ->sum('credits');

        $summary[] = [
          'semester' => $sem,
          'session' => $result->examSession->name ?? '',
          'credits_earned' => $credits,
          'sgpa' => $result->sgpa,
          'result_status' => $result->result_status,
        ];
      }
      return $summary;
    }

    return $histories->map(function ($h) {
      return [
        'semester' => $h->semester,
        'credits_earned' => $h->total_credits_earned,
        'sgpa' => $h->sgpa,
        'result_status' => $h->result_status,
        'promotion_status' => $h->promotion_status,
      ];
    })->toArray();
  }
}
