<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\FacultyProfile;
use App\Models\ExamSystem\InvigilationDuty;
use App\Models\ExamSystem\EvaluationDuty;
use App\Models\ExamSystem\ModerationDuty;

class FacultyWorkloadService
{
  // Example max limits
  protected $maxInvigilation = 5;
  protected $maxEvaluation = 100;
  protected $maxModeration = 3;

  /**
   * Get workload summary for a faculty.
   */
  public function getFacultyLoad($facultyId): array
  {
    $invigilation = InvigilationDuty::where('faculty_id', $facultyId)->count();
    $evaluation = EvaluationDuty::where('faculty_id', $facultyId)->sum('copies_assigned');
    $moderation = ModerationDuty::where('faculty_id', $facultyId)->count();
    return [
      'invigilation_count' => $invigilation,
      'evaluation_load' => $evaluation,
      'moderation_count' => $moderation,
      'invigilation_ok' => $invigilation < $this->maxInvigilation,
      'evaluation_ok' => $evaluation < $this->maxEvaluation,
      'moderation_ok' => $moderation < $this->maxModeration,
    ];
  }

  /**
   * Assign a duty if faculty is available (not overloaded).
   * $type: invigilation|evaluation|moderation
   * $params: array of assignment params
   */
  public function assignIfAvailable($facultyId, $type, $params)
  {
    $load = $this->getFacultyLoad($facultyId);
    if ($type === 'invigilation' && $load['invigilation_ok']) {
      return InvigilationDuty::create(array_merge(['faculty_id' => $facultyId], $params));
    }
    if ($type === 'evaluation' && $load['evaluation_ok']) {
      return EvaluationDuty::create(array_merge(['faculty_id' => $facultyId], $params));
    }
    if ($type === 'moderation' && $load['moderation_ok']) {
      return ModerationDuty::create(array_merge(['faculty_id' => $facultyId], $params));
    }
    return null; // Not assigned due to overload
  }
}
