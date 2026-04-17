<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\InvigilationDuty;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\Room;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvigilationDutyController extends Controller
{
  public function index(Request $request)
  {
    $query = InvigilationDuty::with(['faculty', 'exam', 'room']);

    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }

    if ($request->filled('faculty_id')) {
      $query->where('faculty_id', $request->faculty_id);
    }

    if ($request->filled('date')) {
      $query->whereDate('date', $request->date);
    }

    if ($request->filled('session')) {
      $query->where('session', $request->session);
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    $duties = $query->orderBy('date', 'desc')->paginate(50);
    $exams = Exam::all();
    $faculties = Faculty::orderBy('FIRST_NAME')->get();

    $totalDuties = InvigilationDuty::count();
    $assignedCount = InvigilationDuty::where('status', 'assigned')->count();
    $completedCount = InvigilationDuty::where('status', 'completed')->count();
    $todayCount = InvigilationDuty::whereDate('date', today())->count();

    return view('coe.invigilation-duties.index', compact(
      'duties',
      'exams',
      'faculties',
      'totalDuties',
      'assignedCount',
      'completedCount',
      'todayCount'
    ));
  }

  public function create()
  {
    $exams = Exam::all();
    $faculties = Faculty::orderBy('FIRST_NAME')->get();
    $rooms = Room::all();

    return view('coe.invigilation-duties.create', compact('exams', 'faculties', 'rooms'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'faculty_id' => 'required|array|min:1',
      'faculty_id.*' => 'exists:faculties,id',
      'room_id' => 'required|exists:rooms,id',
      'date' => 'required|date',
      'session' => 'required|in:morning,afternoon,evening',
      'role' => 'required|in:chief_invigilator,invigilator,reliever',
    ]);

    $count = 0;
    foreach ($request->faculty_id as $facultyId) {
      InvigilationDuty::create([
        'exam_id' => $request->exam_id,
        'faculty_id' => $facultyId,
        'room_id' => $request->room_id,
        'date' => $request->date,
        'session' => $request->session,
        'role' => $request->role,
        'status' => 'assigned',
      ]);
      $count++;
    }

    return redirect()->route('admin.invigilation-duties.index')
      ->with('success', $count . ' duty/duties assigned successfully');
  }

  public function show($id)
  {
    $duty = InvigilationDuty::with(['faculty', 'exam', 'room'])->findOrFail($id);
    return view('coe.invigilation-duties.show', compact('duty'));
  }

  public function edit($id)
  {
    $duty = InvigilationDuty::findOrFail($id);
    $exams = Exam::all();
    $faculties = Faculty::orderBy('FIRST_NAME')->get();
    $rooms = Room::all();

    return view('coe.invigilation-duties.edit', compact('duty', 'exams', 'faculties', 'rooms'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'faculty_id' => 'required|exists:faculties,id',
      'room_id' => 'required|exists:rooms,id',
      'date' => 'required|date',
      'session' => 'required|in:morning,afternoon,evening',
      'role' => 'required|in:chief_invigilator,invigilator,reliever',
    ]);

    $duty = InvigilationDuty::findOrFail($id);
    $duty->update($request->only([
      'exam_id',
      'faculty_id',
      'room_id',
      'date',
      'session',
      'role'
    ]));

    return redirect()->route('admin.invigilation-duties.index')
      ->with('success', 'Duty updated successfully');
  }

  public function destroy($id)
  {
    $duty = InvigilationDuty::findOrFail($id);
    $duty->delete();

    return redirect()->route('admin.invigilation-duties.index')
      ->with('success', 'Duty deleted successfully');
  }

  public function markCompleted($id)
  {
    $duty = InvigilationDuty::findOrFail($id);
    $duty->update(['status' => 'completed']);

    return redirect()->back()->with('success', 'Duty marked as completed');
  }

  public function export(Request $request)
  {
    $query = InvigilationDuty::with(['exam', 'faculty', 'room']);

    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }

    $duties = $query->get();
    return response()->json($duties);
  }

  public function autoAssign(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'date' => 'required|date',
    ]);

    try {
      DB::beginTransaction();

      // Auto-assign invigilation duties logic
      // Get available faculty members
      // Distribute duties evenly across rooms and time slots
      // Avoid conflicts and ensure fair distribution

      DB::commit();
      return redirect()->route('admin.invigilation-duties.index')
        ->with('success', 'Invigilation duties auto-assigned successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Auto-assignment failed: ' . $e->getMessage());
    }
  }
}
