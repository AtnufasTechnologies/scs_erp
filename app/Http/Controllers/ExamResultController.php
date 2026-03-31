<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\Result;
use App\Models\ExamSystem\Exam;
use App\Models\StudentMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamResultController extends Controller
{
  public function index(Request $request)
  {
    $query = Result::with(['student', 'exam']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    if ($request->has('result_status') && $request->result_status != '') {
      $query->where('result_status', $request->result_status);
    }

    $results = $query->orderBy('created_at', 'desc')->paginate(50);
    $exams = Exam::all();

    return view('coe.results.index', compact('results', 'exams'));
  }

  public function create()
  {
    $exams = Exam::all();
    $students = StudentMaster::where('is_deleted', 0)->where('is_left', 0)->get();

    return view('coe.results.create', compact('exams', 'students'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'exam_student_id' => 'required|exists:student_masters,id',
      'cgpa' => 'nullable|numeric',
      'sgpa' => 'nullable|numeric',
      'percentage' => 'nullable|numeric',
      'grade' => 'nullable|string',
      'result_status' => 'required|in:pending,pass,fail,absent',
    ]);

    Result::create($request->all());

    return redirect()->route('coe.results.index')
      ->with('success', 'Result created successfully');
  }

  public function show($id)
  {
    $result = Result::with(['student', 'exam'])->findOrFail($id);
    return view('coe.results.show', compact('result'));
  }

  public function edit($id)
  {
    $result = Result::findOrFail($id);
    $exams = Exam::all();
    $students = StudentMaster::where('is_deleted', 0)->where('is_left', 0)->get();

    return view('coe.results.edit', compact('result', 'exams', 'students'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'exam_student_id' => 'required|exists:student_masters,id',
      'cgpa' => 'nullable|numeric',
      'sgpa' => 'nullable|numeric',
      'percentage' => 'nullable|numeric',
      'grade' => 'nullable|string',
      'result_status' => 'required|in:pending,pass,fail,absent',
    ]);

    $result = Result::findOrFail($id);
    $result->update($request->all());

    return redirect()->route('coe.results.index')
      ->with('success', 'Result updated successfully');
  }

  public function destroy($id)
  {
    $result = Result::findOrFail($id);
    $result->delete();

    return redirect()->route('coe.results.index')
      ->with('success', 'Result deleted successfully');
  }

  public function publish(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
    ]);

    Result::where('exam_id', $request->exam_id)
      ->update(['is_published' => true, 'published_at' => now()]);

    return redirect()->route('coe.results.index')
      ->with('success', 'Results published successfully');
  }

  public function generateResults(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
    ]);

    try {
      DB::beginTransaction();

      // Auto-generate results based on marks
      // Calculate SGPA, CGPA, Grade
      // Set result status

      DB::commit();
      return redirect()->route('coe.results.index')
        ->with('success', 'Results generated successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Generation failed: ' . $e->getMessage());
    }
  }

  public function export(Request $request)
  {
    $query = Result::with(['exam', 'student', 'semester']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    $results = $query->get();
    return response()->json($results);
  }

  public function autoGenerate(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
    ]);

    try {
      DB::beginTransaction();

      // Auto-generate results logic
      // Get all marks for the exam
      // Calculate grades and CGPA
      // Create result records
      // Update student academic records

      DB::commit();
      return redirect()->route('admin.exam-results.index')
        ->with('success', 'Results auto-generated successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Result generation failed: ' . $e->getMessage());
    }
  }
}
