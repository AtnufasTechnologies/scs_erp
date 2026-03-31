<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExamSystem\InvigilationService;
use App\Models\ExamSystem\InvigilationDuty;
use App\Models\ExamSystem\FacultyProfile;
use App\Models\ExamSystem\Room;
use Barryvdh\DomPDF\Facade\Pdf;

class InvigilationController extends Controller
{
  protected $service;

  public function __construct(InvigilationService $service)
  {
    $this->service = $service;
  }

  // Assign duties for an exam
  public function assign(Request $request)
  {
    $examId = $request->input('exam_id');
    $userId = $request->user()->id;
    try {
      $this->service->assignInvigilators($examId, $userId);
      return response()->json(['message' => 'Duties assigned successfully.']);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 403);
    }
  }

  // View duty chart with filters
  public function dutyChart(Request $request)
  {
    $query = InvigilationDuty::query();
    if ($request->filled('date')) {
      $query->where('date', $request->input('date'));
    }
    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->input('exam_id'));
    }
    if ($request->filled('faculty_id')) {
      $query->where('faculty_id', $request->input('faculty_id'));
    }
    if ($request->filled('room_id')) {
      $query->where('room_id', $request->input('room_id'));
    }
    $duties = $query->with(['faculty', 'exam'])->orderBy('date')->orderBy('session')->get();
    return response()->json($duties);
  }

  // Download invigilation schedule as PDF
  public function downloadSchedule(Request $request)
  {
    $query = InvigilationDuty::query();
    if ($request->filled('date')) {
      $query->where('date', $request->input('date'));
    }
    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->input('exam_id'));
    }
    $duties = $query->with(['faculty', 'exam', 'room'])->orderBy('date')->orderBy('session')->get();
    $pdf = Pdf::loadView('invigilation.schedule', ['duties' => $duties]);
    return $pdf->download('invigilation_schedule.pdf');
  }

  // Faculty login: view own duties
  public function myDuties(Request $request)
  {
    $facultyId = auth()->user()->faculty_profile_id ?? $request->input('faculty_id');
    $duties = $this->service->getFacultySchedule($facultyId);
    return response()->json($duties);
  }
}
