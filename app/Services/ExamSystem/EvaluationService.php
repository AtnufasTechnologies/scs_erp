<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\EvaluationDuty;
use App\Models\ExamSystem\FacultyProfile;
use Illuminate\Support\Facades\DB;

class EvaluationService
{
  protected $maxCopiesPerFaculty = 100; // Example max limit

  /**
   * Assign answer scripts to evaluators for a subject in an exam.
   */
  public function assignEvaluation($examId, $subjectId, $totalCopies)
  {
    $faculties = FacultyProfile::all();
    $copiesLeft = $totalCopies;
    $facultyIds = $faculties->pluck('id')->toArray();
    shuffle($facultyIds); // Randomize for fairness
    foreach ($facultyIds as $facultyId) {
      if ($copiesLeft <= 0) break;
      $alreadyAssigned = EvaluationDuty::where('exam_id', $examId)
        ->where('subject_id', $subjectId)
        ->where('faculty_id', $facultyId)
        ->sum('copies_assigned');
      $assignable = min($this->maxCopiesPerFaculty - $alreadyAssigned, $copiesLeft);
      if ($assignable > 0) {
        EvaluationDuty::create([
          'exam_id' => $examId,
          'faculty_id' => $facultyId,
          'subject_id' => $subjectId,
          'copies_assigned' => $assignable,
          'copies_evaluated' => 0,
          'status' => 'pending',
        ]);
        $copiesLeft -= $assignable;
      }
    }
  }

  /**
   * Update progress (increment evaluated copies).
   */
  public function updateProgress($dutyId, $count = 1)
  {
    $duty = EvaluationDuty::findOrFail($dutyId);
    $duty->copies_evaluated = min($duty->copies_evaluated + $count, $duty->copies_assigned);
    $duty->status = $duty->copies_evaluated >= $duty->copies_assigned ? 'completed' : 'in_progress';
    $duty->save();
    return $duty;
  }

  /**
   * Mark evaluation as completed.
   */
  public function completeEvaluation($dutyId)
  {
    $duty = EvaluationDuty::findOrFail($dutyId);
    $duty->copies_evaluated = $duty->copies_assigned;
    $duty->status = 'completed';
    $duty->save();
    return $duty;
  }
}
