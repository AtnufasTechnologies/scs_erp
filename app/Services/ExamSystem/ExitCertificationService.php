<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\StudentCredit;
use App\Models\ExamSystem\StudentExitRecord;
use Illuminate\Support\Facades\DB;

class ExitCertificationService
{
  // Example thresholds (customize as needed)
  protected $thresholds = [
    1 => 40,   // Year 1: Certificate
    2 => 80,   // Year 2: Diploma
    3 => 120,  // Year 3: Degree
    4 => 160,  // Year 4: Honors
  ];

  /**
   * Check eligibility and return exit level if eligible, else null.
   */
  public function checkEligibility($studentId): ?string
  {
    $totalCredits = StudentCredit::where('exam_student_id', $studentId)->sum('credits_earned');
    foreach (array_reverse($this->thresholds, true) as $year => $threshold) {
      if ($totalCredits >= $threshold) {
        return match ($year) {
          4 => 'Honors',
          3 => 'Degree',
          2 => 'Diploma',
          1 => 'Certificate',
          default => null,
        };
      }
    }
    return null;
  }

  /**
   * Generate exit certificate record if eligible.
   */
  public function generateCertificate($studentId): ?StudentExitRecord
  {
    $exitLevel = $this->checkEligibility($studentId);
    if (!$exitLevel) {
      return null;
    }
    $record = StudentExitRecord::firstOrCreate(
      [
        'exam_student_id' => $studentId,
        'exit_type' => $exitLevel,
      ],
      [
        'exit_date' => now(),
        'certificate_no' => $this->generateCertificateNumber($studentId, $exitLevel),
      ]
    );
    return $record;
  }

  /**
   * Generate a unique certificate number.
   */
  protected function generateCertificateNumber($studentId, $exitLevel): string
  {
    return strtoupper($exitLevel) . '-' . $studentId . '-' . now()->format('YmdHis');
  }

  /**
   * Check if certificate is already generated.
   */
  public function isCertificateGenerated($studentId, $exitLevel): bool
  {
    return StudentExitRecord::where('exam_student_id', $studentId)
      ->where('exit_type', $exitLevel)
      ->exists();
  }
}
