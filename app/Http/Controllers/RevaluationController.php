<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\Result;
use App\Models\ExamSystem\ResultSubject;
use App\Models\ExamSystem\ResultLock;
use App\Models\ExamSystem\GradeMapping;
use App\Models\ExamSystem\ExamSubjectMaster;
use App\Services\ExamSystem\CreditEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevaluationController extends Controller
{
  /**
   * Apply revaluation — update marks for a subject and recalculate credits/CGPA.
   * Only works on published results that are not locked.
   */
  public function apply(Request $request)
  {
    $request->validate([
      'result_id' => 'required|exists:results,id',
      'erp_subject_id' => 'required|integer',
      'new_marks' => 'required|numeric|min:0|max:100',
      'reason' => 'nullable|string|max:500',
    ]);

    $result = Result::with(['student', 'examSession', 'resultSubjects'])->findOrFail($request->result_id);

    if (!$result->is_published) {
      return response()->json(['error' => 'Cannot revalue unpublished results.'], 422);
    }

    if (ResultLock::isLocked($result->exam_session_id)) {
      return response()->json(['error' => 'Cannot revalue — session results are locked.'], 422);
    }

    $resultSubject = ResultSubject::where('result_id', $result->id)
      ->where('erp_subject_id', $request->erp_subject_id)
      ->first();

    if (!$resultSubject) {
      return response()->json(['error' => 'Subject not found in this result.'], 404);
    }

    try {
      DB::beginTransaction();

      $oldMarks = $resultSubject->total_marks;
      $newMarks = $request->new_marks;

      // Recalculate grade for new marks
      $subjectMaster = ExamSubjectMaster::where('erp_subject_id', $request->erp_subject_id)->first();
      $programId = $subjectMaster->program_id ?? $result->student->program_id;

      $gradeMapping = GradeMapping::where('program_id', $programId)
        ->where('min_marks', '<=', $newMarks)
        ->where('max_marks', '>=', $newMarks)
        ->first();

      $newGrade = $gradeMapping ? $gradeMapping->grade : 'F';
      $newGradePoint = $gradeMapping ? $gradeMapping->grade_point : 0;

      // Update the subject result
      $resultSubject->update([
        'total_marks' => $newMarks,
        'grade' => $newGrade,
        'grade_point' => $newGradePoint,
        'result_status' => $newGrade === 'F' ? 'Normal' : $resultSubject->result_status,
      ]);

      // Reload result subjects
      $result->load('resultSubjects');

      // Recalculate via CreditEngineService
      $creditEngine = new CreditEngineService();
      $recalcResult = $creditEngine->recalculateForRevaluation($result);

      DB::commit();

      return response()->json([
        'message' => 'Revaluation applied successfully.',
        'old_marks' => $oldMarks,
        'new_marks' => $newMarks,
        'new_grade' => $newGrade,
        'new_grade_point' => $newGradePoint,
        'sgpa' => $recalcResult['sgpa'],
        'cgpa' => $recalcResult['cgpa'],
        'earned_credits' => $recalcResult['earned_credits'],
        'result_status' => $recalcResult['result_status'],
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['error' => 'Revaluation failed: ' . $e->getMessage()], 500);
    }
  }
}
