<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamSystem\EvaluationDuty;
use App\Services\RemunerationService;

class EvaluationDutyStatusController extends Controller
{
  public function markCompleted(Request $request, $dutyId)
  {
    $duty = EvaluationDuty::findOrFail($dutyId);
    if ($duty->status === 'completed') {
      return response()->json(['message' => 'Duty already marked as completed.'], 200);
    }
    $duty->status = 'completed';
    $duty->save();

    // Prevent duplicate remuneration
    $exists = \App\Models\FacultyRemuneration::where([
      ['faculty_id', '=', $duty->faculty_id],
      ['duty_type', '=', 'evaluation'],
      ['reference_id', '=', $duty->id],
    ])->exists();
    if (!$exists) {
      $remService = new RemunerationService();
      $remService->generateRemuneration([
        'faculty_id' => $duty->faculty_id,
        'duty_type' => 'evaluation',
        'reference_id' => $duty->id,
        'quantity' => $duty->copies_evaluated ?? 1,
      ]);
    }
    return response()->json(['message' => 'Duty marked as completed and remuneration generated.']);
  }
}
