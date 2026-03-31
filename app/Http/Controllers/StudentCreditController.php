<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\StudentCredit;
use App\Models\StudentMaster;
use App\Models\ProgramCourseMaster;
use Illuminate\Http\Request;

class StudentCreditController extends Controller
{
  public function index(Request $request)
  {
    $query = StudentCredit::with(['student', 'subject']);

    if ($request->has('student_id') && $request->student_id != '') {
      $query->where('exam_student_id', $request->student_id);
    }

    if ($request->has('semester') && $request->semester != '') {
      $query->where('semester', $request->semester);
    }

    $credits = $query->orderBy('created_at', 'desc')->paginate(50);
    $students = StudentMaster::where('is_deleted', 0)->where('is_left', 0)->get();

    return view('coe.student-credits.index', compact('credits', 'students'));
  }

  public function create()
  {
    $students = StudentMaster::where('is_deleted', 0)->where('is_left', 0)->get();
    $subjects = ProgramCourseMaster::all();

    return view('coe.student-credits.create', compact('students', 'subjects'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_student_id' => 'required|exists:student_masters,id',
      'subject_id' => 'required',
      'semester' => 'required|integer',
      'credits_earned' => 'required|numeric',
      'grade' => 'nullable|string',
    ]);

    StudentCredit::create($request->all());

    return redirect()->route('coe.student-credits.index')
      ->with('success', 'Student credit entry created successfully');
  }

  public function show($id)
  {
    $credit = StudentCredit::with(['student', 'subject'])->findOrFail($id);
    return view('coe.student-credits.show', compact('credit'));
  }

  public function edit($id)
  {
    $credit = StudentCredit::findOrFail($id);
    $students = StudentMaster::where('is_deleted', 0)->where('is_left', 0)->get();
    $subjects = ProgramCourseMaster::all();

    return view('coe.student-credits.edit', compact('credit', 'students', 'subjects'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_student_id' => 'required|exists:student_masters,id',
      'subject_id' => 'required',
      'semester' => 'required|integer',
      'credits_earned' => 'required|numeric',
      'grade' => 'nullable|string',
    ]);

    $credit = StudentCredit::findOrFail($id);
    $credit->update($request->all());

    return redirect()->route('coe.student-credits.index')
      ->with('success', 'Student credit updated successfully');
  }

  public function destroy($id)
  {
    $credit = StudentCredit::findOrFail($id);
    $credit->delete();

    return redirect()->route('coe.student-credits.index')
      ->with('success', 'Student credit deleted successfully');
  }

  public function transcript($studentId)
  {
    $student = StudentMaster::findOrFail($studentId);
    $credits = StudentCredit::with('subject')
      ->where('exam_student_id', $studentId)
      ->orderBy('semester')
      ->get();

    return view('coe.student-credits.transcript', compact('student', 'credits'));
  }

  public function export(Request $request)
  {
    $query = StudentCredit::with(['student', 'subject', 'semester']);

    if ($request->has('student_id') && $request->student_id != '') {
      $query->where('exam_student_id', $request->student_id);
    }

    $credits = $query->get();
    return response()->json($credits);
  }
}
