<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\ModerationDuty;
use App\Models\ExamSystem\FacultyProfile;
use App\Models\ExamSystem\Mark;
use App\Models\ExamSystem\Subject;
use App\Models\ExamSystem\Program;
use Illuminate\Support\Facades\DB;

class ModerationService
{
  // Default threshold if not set per program
  protected $defaultDeviationThreshold = 10;

  /**
   * Assign moderators for a subject (internal and external).
   */
  public function assignModerator($examId, $subjectId, $type = 'internal')
  {
    $faculties = FacultyProfile::all();
    $facultyIds = $faculties->pluck('id')->toArray();
    shuffle($facultyIds); // Randomize for fairness
    $moderatorId = reset($facultyIds);
    ModerationDuty::create([
      'exam_id' => $examId,
      'faculty_id' => $moderatorId,
      'subject_id' => $subjectId,
      'moderation_type' => $type,
      'status' => 'pending',
    ]);
    return $moderatorId;
  }

  /**
   * Apply moderation rules: adjust marks if deviation exceeds threshold.
   */
  /**
   * Apply moderation rules: compare evaluator and moderator marks, adjust if difference exceeds threshold.
   * Threshold is configurable per program.
   */
  public function applyModerationRules($examId, $subjectId)
  {
    $subject = Subject::findOrFail($subjectId);
    $program = Program::find($subject->program_id);
    $threshold = $program && property_exists($program, 'moderation_threshold') && $program->moderation_threshold
      ? $program->moderation_threshold
      : $this->defaultDeviationThreshold;

    // Assume Mark model has evaluator_marks and moderator_marks columns
    $marks = Mark::where('exam_id', $examId)
      ->where('exam_subject_id', $subjectId)
      ->get();

    foreach ($marks as $mark) {
      $evaluator = $mark->evaluator_marks ?? $mark->total_marks;
      $moderator = $mark->moderator_marks ?? $evaluator;
      $diff = abs($evaluator - $moderator);
      if ($diff > $threshold) {
        // Adjust: set final marks as average
        $final = round(($evaluator + $moderator) / 2, 2);
      } else {
        // Within threshold, use evaluator's marks
        $final = $evaluator;
      }
      $mark->total_marks = $final;
      $mark->save();
    }
  }

  /**
   * Finalize marks after moderation.
   */
  public function finalizeMarks($examId, $subjectId)
  {
    // Example: set a flag or status if needed
    DB::table('marks')
      ->where('exam_id', $examId)
      ->where('exam_subject_id', $subjectId)
      ->update(['moderated' => true]);
  }
}
