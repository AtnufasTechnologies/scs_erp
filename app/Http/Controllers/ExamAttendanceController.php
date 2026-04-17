<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\ExamAttendance;
use App\Models\ExamSystem\Exam;
use App\Models\StudentMaster;
use Illuminate\Http\Request;

class ExamAttendanceController extends Controller
{
  public function index(Request $request)
  {
    $query = ExamAttendance::with(['student', 'exam']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    if ($request->has('date')) {
      $query->whereDate('date', $request->date);
    }

    if ($request->has('status') && $request->status != '') {
      $query->where('status', $request->status);
    }

    $attendances = $query->orderBy('date', 'desc')->paginate(50);
    $exams = Exam::all();

    return view('coe.attendance.index', compact('attendances', 'exams'));
  }

  public function create()
  {
    $exams = Exam::all();
    $students = StudentMaster::where('is_deleted', 0)->where('is_left', 0)->get();

    return view('coe.attendance.create', compact('exams', 'students'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'exam_student_id' => 'required|exists:student_masters,id',
      'date' => 'required|date',
      'status' => 'required|in:present,absent,late',
    ]);

    ExamAttendance::create($request->all());

    return redirect()->route('coe.exam-attendance.index')
      ->with('success', 'Attendance marked successfully');
  }

  public function show($id)
  {
    $attendance = ExamAttendance::with(['student', 'exam'])->findOrFail($id);
    return view('coe.attendance.show', compact('attendance'));
  }

  public function edit($id)
  {
    $attendance = ExamAttendance::findOrFail($id);
    $exams = Exam::all();
    $students = StudentMaster::where('is_deleted', 0)->where('is_left', 0)->get();

    return view('coe.attendance.edit', compact('attendance', 'exams', 'students'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'exam_student_id' => 'required|exists:student_masters,id',
      'date' => 'required|date',
      'status' => 'required|in:present,absent,late',
    ]);

    $attendance = ExamAttendance::findOrFail($id);
    $attendance->update($request->all());

    return redirect()->route('coe.exam-attendance.index')
      ->with('success', 'Attendance updated successfully');
  }

  public function destroy($id)
  {
    $attendance = ExamAttendance::findOrFail($id);
    $attendance->delete();

    return redirect()->route('coe.exam-attendance.index')
      ->with('success', 'Attendance record deleted successfully');
  }

  public function bulkMark(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'date' => 'required|date',
      'attendance_data' => 'required|array',
    ]);

    foreach ($request->attendance_data as $studentId => $status) {
      ExamAttendance::updateOrCreate(
        [
          'exam_id' => $request->exam_id,
          'exam_student_id' => $studentId,
          'date' => $request->date,
        ],
        ['status' => $status]
      );
    }

    return redirect()->route('coe.exam-attendance.index')
      ->with('success', 'Bulk attendance marked successfully');
  }

  public function export(Request $request)
  {
    $query = ExamAttendance::with(['exam', 'student']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    $attendances = $query->get();
    return response()->json($attendances);
  }
}
