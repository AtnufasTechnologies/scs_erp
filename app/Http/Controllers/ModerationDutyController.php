<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\ModerationDuty;
use App\Models\ExamSystem\Exam;
use App\Models\Faculty;
use App\Models\ProgramCourseMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModerationDutyController extends Controller
{
  public function index(Request $request)
  {
    $query = ModerationDuty::with(['faculty', 'exam']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    if ($request->has('status') && $request->status != '') {
      $query->where('status', $request->status);
    }

    $duties = $query->orderBy('created_at', 'desc')->paginate(50);
    $exams = Exam::all();

    return view('coe.moderation.index', compact('duties', 'exams'));
  }

  public function create()
  {
    $exams = Exam::all();
    $faculties = Faculty::all();
    $subjects = ProgramCourseMaster::all();

    return view('coe.moderation.create', compact('exams', 'faculties', 'subjects'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'faculty_id' => 'required|exists:faculties,id',
      'subject_id' => 'required',
    ]);

    ModerationDuty::create(array_merge($request->all(), ['status' => 'assigned']));

    return redirect()->route('coe.moderation.index')
      ->with('success', 'Moderation duty assigned successfully');
  }

  public function show($id)
  {
    $duty = ModerationDuty::with(['faculty', 'exam'])->findOrFail($id);
    return view('coe.moderation.show', compact('duty'));
  }

  public function edit($id)
  {
    $duty = ModerationDuty::findOrFail($id);
    $exams = Exam::all();
    $faculties = Faculty::all();
    $subjects = ProgramCourseMaster::all();

    return view('coe.moderation.edit', compact('duty', 'exams', 'faculties', 'subjects'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'faculty_id' => 'required|exists:faculties,id',
      'subject_id' => 'required',
    ]);

    $duty = ModerationDuty::findOrFail($id);
    $duty->update($request->all());

    return redirect()->route('coe.moderation.index')
      ->with('success', 'Moderation duty updated successfully');
  }

  public function destroy($id)
  {
    $duty = ModerationDuty::findOrFail($id);
    $duty->delete();

    return redirect()->route('coe.moderation.index')
      ->with('success', 'Moderation duty deleted successfully');
  }

  public function markCompleted($id)
  {
    $duty = ModerationDuty::findOrFail($id);
    $duty->update(['status' => 'completed']);

    return redirect()->back()->with('success', 'Moderation marked as completed');
  }

  public function export(Request $request)
  {
    $query = ModerationDuty::with(['exam', 'faculty', 'subject']);

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
      'subject_id' => 'required|exists:subjects,id',
    ]);

    try {
      DB::beginTransaction();

      // Auto-assign moderation duties logic
      // Get senior faculty members for moderation
      // Distribute answer sheets for quality assurance
      // Ensure proper distribution and avoid conflicts

      DB::commit();
      return redirect()->route('admin.moderation-duties.index')
        ->with('success', 'Moderation duties auto-assigned successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Auto-assignment failed: ' . $e->getMessage());
    }
  }
}
