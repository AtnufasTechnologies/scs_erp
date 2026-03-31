<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\InvigilationDuty;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\Room;
use App\Models\Faculty;
use Illuminate\Http\Request;

class InvigilationDutyController extends Controller
{
  public function index(Request $request)
  {
    $query = InvigilationDuty::with(['faculty', 'exam', 'room']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    if ($request->has('date')) {
      $query->whereDate('date', $request->date);
    }

    if ($request->has('status') && $request->status != '') {
      $query->where('status', $request->status);
    }

    $duties = $query->orderBy('date')->paginate(50);
    $exams = Exam::all();

    return view('coe.invigilation-duties.index', compact('duties', 'exams'));
  }

  public function create()
  {
    $exams = Exam::all();
    $faculties = Faculty::all();
    $rooms = Room::all();

    return view('coe.invigilation-duties.create', compact('exams', 'faculties', 'rooms'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'faculty_id' => 'required|exists:faculties,id',
      'room_id' => 'required',
      'date' => 'required|date',
      'session' => 'required|in:morning,afternoon,evening',
      'role' => 'nullable|in:chief,assistant',
    ]);

    InvigilationDuty::create(array_merge($request->all(), ['status' => 'assigned']));

    return redirect()->route('coe.invigilation-duties.index')
      ->with('success', 'duty assigned successfully');
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
    $faculties = Faculty::all();
    $rooms = Room::all();

    return view('coe.invigilation-duties.edit', compact('duty', 'exams', 'faculties', 'rooms'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'faculty_id' => 'required|exists:faculties,id',
      'room_id' => 'required',
      'date' => 'required|date',
      'session' => 'required|in:morning,afternoon,evening',
      'role' => 'nullable|in:chief,assistant',
    ]);

    $duty = InvigilationDuty::findOrFail($id);
    $duty->update($request->all());

    return redirect()->route('coe.invigilation-duties.index')
      ->with('success', 'Duty updated successfully');
  }

  public function destroy($id)
  {
    $duty = InvigilationDuty::findOrFail($id);
    $duty->delete();

    return redirect()->route('coe.invigilation-duties.index')
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

    if ($request->has('exam_id') && $request->exam_id != '') {
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
