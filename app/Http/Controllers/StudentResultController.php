<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\Result;
use App\Models\ExamSystem\Student;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
  public function lookup()
  {
    return view('student.results.lookup');
  }

  public function search(Request $request)
  {
    $request->validate([
      'enrollment_no' => 'required|string|max:50',
    ]);

    $enrollmentNo = trim($request->enrollment_no);

    $examStudent = Student::where('enrollment_no', $enrollmentNo)->first();

    if (!$examStudent) {
      return back()->withInput()->with('error', 'No student found with this enrollment number.');
    }

    // Only show published results
    $results = Result::with(['exam', 'examSession', 'resultSubjects'])
      ->where('exam_student_id', $examStudent->id)
      ->where('is_published', true)
      ->orderBy('created_at', 'desc')
      ->get();

    if ($results->isEmpty()) {
      return back()->withInput()->with('error', 'No published results found for this enrollment number.');
    }

    return view('student.results.view', compact('results', 'examStudent', 'enrollmentNo'));
  }

  public function detail($id, Request $request)
  {
    $request->validate([
      'enrollment_no' => 'required|string|max:50',
    ]);

    $enrollmentNo = trim($request->enrollment_no);
    $examStudent = Student::where('enrollment_no', $enrollmentNo)->firstOrFail();

    // Only allow viewing published results that belong to this student
    $result = Result::with(['student', 'exam', 'examSession', 'resultSubjects'])
      ->where('id', $id)
      ->where('exam_student_id', $examStudent->id)
      ->where('is_published', true)
      ->firstOrFail();

    return view('student.results.detail', compact('result', 'enrollmentNo'));
  }
}
