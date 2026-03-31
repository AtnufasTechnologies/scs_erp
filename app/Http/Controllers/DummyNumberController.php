<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\DummyNumber;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\ExamStudent;
use App\Models\StudentMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DummyNumberController extends Controller
{
  public function index(Request $request)
  {
    $query = DummyNumber::with(['exam', 'examStudent.student']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    if ($request->has('locked')) {
      $query->where('locked', $request->locked);
    }

    $dummyNumbers = $query->orderBy('dummy_number')->paginate(50);
    $exams = Exam::all();

    return view('coe.dummy-numbers.index', compact('dummyNumbers', 'exams'));
  }

  public function create()
  {
    $exams = Exam::all();
    $students = ExamStudent::with('student')->where('status', 'active')->get();

    return view('coe.dummy-numbers.create', compact('exams', 'students'));
  }

  /**
   * Generate a unique dummy number for a given exam
   */
  private function generateDummyNumber($examId)
  {
    $prefix = 'DN' . date('Y');
    $lastDummy = DummyNumber::where('exam_id', $examId)
      ->where('dummy_number', 'like', $prefix . '%')
      ->orderBy('dummy_number', 'desc')
      ->first();

    if ($lastDummy) {
      $lastSeq = (int) substr($lastDummy->dummy_number, strlen($prefix));
      $nextSeq = $lastSeq + 1;
    } else {
      $nextSeq = 1;
    }

    return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'exam_student_id' => 'required|exists:exam_students,id',
    ]);

    // Check if student already has a dummy number for this exam
    $exists = DummyNumber::where('exam_id', $request->exam_id)
      ->where('exam_student_id', $request->exam_student_id)
      ->exists();

    if ($exists) {
      return redirect()->back()
        ->withInput()
        ->with('error', 'This student already has a dummy number for the selected exam.');
    }

    $dummyNumber = $this->generateDummyNumber($request->exam_id);

    DummyNumber::create([
      'exam_id' => $request->exam_id,
      'exam_student_id' => $request->exam_student_id,
      'dummy_number' => $dummyNumber,
    ]);

    return redirect()->route('coe.dummy-numbers.index')
      ->with('success', 'Dummy number ' . $dummyNumber . ' assigned successfully');
  }

  public function show($id)
  {
    $dummyNumber = DummyNumber::with(['exam', 'examStudent.student'])->findOrFail($id);
    return view('coe.dummy-numbers.show', compact('dummyNumber'));
  }

  public function edit($id)
  {
    $dummyNumber = DummyNumber::findOrFail($id);
    $exams = Exam::all();
    $students = ExamStudent::with('student')->where('status', 'active')->get();

    return view('coe.dummy-numbers.edit', compact('dummyNumber', 'exams', 'students'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'exam_student_id' => 'required|exists:exam_students,id',
    ]);

    $dummyNumber = DummyNumber::findOrFail($id);
    $dummyNumber->update([
      'exam_id' => $request->exam_id,
      'exam_student_id' => $request->exam_student_id,
    ]);

    return redirect()->route('coe.dummy-numbers.index')
      ->with('success', 'Dummy number updated successfully');
  }

  public function destroy($id)
  {
    $dummyNumber = DummyNumber::findOrFail($id);
    $dummyNumber->delete();

    return redirect()->route('coe.dummy-numbers.index')
      ->with('success', 'Dummy number deleted successfully');
  }

  public function lock(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
    ]);

    DummyNumber::where('exam_id', $request->exam_id)
      ->update(['is_locked' => true]);

    return redirect()->route('coe.dummy-numbers.index')
      ->with('success', 'Dummy numbers locked successfully');
  }

  public function unlock(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
    ]);

    DummyNumber::where('exam_id', $request->exam_id)
      ->update(['is_locked' => false]);

    return redirect()->route('coe.dummy-numbers.index')
      ->with('success', 'Dummy numbers unlocked successfully');
  }

  public function autoGenerate(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
    ]);

    try {
      DB::beginTransaction();

      $examId = $request->exam_id;

      // Get all registered exam_students for this exam that don't already have a dummy number
      $registeredStudentIds = DB::table('exam_registrations')
        ->where('exam_id', $examId)
        ->where('status', 'approved')
        ->pluck('exam_student_id');

      $existingStudentIds = DummyNumber::where('exam_id', $examId)
        ->pluck('exam_student_id');

      $newStudentIds = $registeredStudentIds->diff($existingStudentIds);

      if ($newStudentIds->isEmpty()) {
        return redirect()->route('coe.dummy-numbers.index')
          ->with('error', 'All registered students already have dummy numbers for this exam.');
      }

      $prefix = 'DN' . date('Y');
      $lastDummy = DummyNumber::where('exam_id', $examId)
        ->where('dummy_number', 'like', $prefix . '%')
        ->orderBy('dummy_number', 'desc')
        ->first();

      $nextSeq = $lastDummy ? ((int) substr($lastDummy->dummy_number, strlen($prefix)) + 1) : 1;

      $records = [];
      foreach ($newStudentIds as $studentId) {
        $records[] = [
          'exam_id' => $examId,
          'exam_student_id' => $studentId,
          'dummy_number' => $prefix . str_pad($nextSeq++, 4, '0', STR_PAD_LEFT),
          'created_at' => now(),
          'updated_at' => now(),
        ];
      }

      DummyNumber::insert($records);

      DB::commit();
      return redirect()->route('coe.dummy-numbers.index')
        ->with('success', count($records) . ' dummy numbers generated successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Generation failed: ' . $e->getMessage());
    }
  }

  public function export(Request $request)
  {
    $query = DummyNumber::with(['exam', 'examStudent.student']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    $dummyNumbers = $query->orderBy('dummy_number')->get();
    return response()->json($dummyNumbers);
  }
}
