<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\InvigilationDuty;
use App\Models\ExamSystem\FacultyProfile;
use App\Models\ExamSystem\EvaluationDuty;
use App\Models\ExamSystem\ModerationDuty;
use Illuminate\Support\Facades\DB;

class ExamReportService
{
  /**
   * Invigilation schedule report with filters.
   */
  public function invigilationSchedule($filters = [])
  {
    $query = InvigilationDuty::with(['faculty', 'exam', 'room']);
    if (!empty($filters['exam_id'])) {
      $query->where('exam_id', $filters['exam_id']);
    }
    if (!empty($filters['faculty_id'])) {
      $query->where('faculty_id', $filters['faculty_id']);
    }
    if (!empty($filters['department'])) {
      $query->whereHas('faculty', function ($q) use ($filters) {
        $q->where('department', $filters['department']);
      });
    }
    return $query->orderBy('date')->orderBy('session')->get();
  }

  /**
   * Faculty workload report with filters.
   */
  public function facultyWorkload($filters = [])
  {
    $query = FacultyProfile::query();
    if (!empty($filters['department'])) {
      $query->where('department', $filters['department']);
    }
    if (!empty($filters['faculty_id'])) {
      $query->where('id', $filters['faculty_id']);
    }
    $faculties = $query->get();
    $report = [];
    foreach ($faculties as $faculty) {
      $report[] = [
        'faculty' => $faculty,
        'invigilation_count' => $faculty->invigilationDuties()->count(),
        'evaluation_load' => $faculty->evaluationDuties()->sum('copies_assigned'),
        'moderation_count' => $faculty->moderationDuties()->count(),
      ];
    }
    return $report;
  }

  /**
   * Evaluation progress report with filters.
   */
  public function evaluationProgress($filters = [])
  {
    $query = EvaluationDuty::with(['faculty', 'exam']);
    if (!empty($filters['exam_id'])) {
      $query->where('exam_id', $filters['exam_id']);
    }
    if (!empty($filters['faculty_id'])) {
      $query->where('faculty_id', $filters['faculty_id']);
    }
    if (!empty($filters['department'])) {
      $query->whereHas('faculty', function ($q) use ($filters) {
        $q->where('department', $filters['department']);
      });
    }
    return $query->orderBy('status')->get();
  }

  /**
   * Moderation summary report with filters.
   */
  public function moderationSummary($filters = [])
  {
    $query = ModerationDuty::with(['faculty', 'exam']);
    if (!empty($filters['exam_id'])) {
      $query->where('exam_id', $filters['exam_id']);
    }
    if (!empty($filters['faculty_id'])) {
      $query->where('faculty_id', $filters['faculty_id']);
    }
    if (!empty($filters['department'])) {
      $query->whereHas('faculty', function ($q) use ($filters) {
        $q->where('department', $filters['department']);
      });
    }
    return $query->orderBy('status')->get();
  }
}
