<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\StudentCredit;
use App\Models\ExamSystem\Student;
use App\Models\ExamSystem\ExamSubjectMaster;
use App\Models\StudentMaster;
use Illuminate\Http\Request;

class StudentCreditController extends Controller
{
  public function index(Request $request)
  {
    $query = StudentCredit::with(['student', 'subject']);

    if ($request->filled('student_id')) {
      $query->where('exam_student_id', $request->student_id);
    }

    if ($request->filled('credit_type')) {
      $query->where('credit_type', $request->credit_type);
    }

    if ($request->filled('semester')) {
      $query->where('semester', $request->semester);
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->whereHas('student', function ($sq) use ($search) {
          $sq->where('enrollment_no', 'like', "%{$search}%");
        })
          ->orWhereHas('subject', function ($sq) use ($search) {
            $sq->where('subject_code', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%");
          })
          ->orWhere('source_institution', 'like', "%{$search}%")
          ->orWhere('source_subject_name', 'like', "%{$search}%");
      });
    }

    $credits = $query->orderBy('created_at', 'desc')->paginate(50);

    // Stats
    $totalCredits = StudentCredit::count();
    $earnedCount = StudentCredit::earned()->count();
    $transferredCount = StudentCredit::transferred()->count();
    $totalEarnedCredits = StudentCredit::earned()->sum('credits_earned');
    $totalTransferredCredits = StudentCredit::transferred()->sum('credits_earned');

    $students = Student::orderBy('enrollment_no')->get();

    return view('coe.student-credits.index', compact(
      'credits',
      'students',
      'totalCredits',
      'earnedCount',
      'transferredCount',
      'totalEarnedCredits',
      'totalTransferredCredits'
    ));
  }

  public function create(Request $request)
  {
    $students = Student::orderBy('enrollment_no')->get();
    $subjects = ExamSubjectMaster::orderBy('subject_code')->get();
    $creditType = $request->get('type', 'earned');

    $studentCredits = null;
    if ($request->filled('student_id')) {
      $studentCredits = StudentCredit::with('subject')
        ->where('exam_student_id', $request->student_id)
        ->orderBy('semester')
        ->orderBy('credit_type')
        ->get();
    }

    return view('coe.student-credits.create', compact('students', 'subjects', 'creditType', 'studentCredits'));
  }

  public function store(Request $request)
  {
    $rules = [
      'exam_student_id' => 'required|exists:exam_students,id',
      'credits_earned' => 'required|numeric|min:0.5|max:30',
      'credit_type' => 'required|in:earned,transferred',
      'semester' => 'required|integer|min:1|max:12',
      'grade' => 'nullable|string|max:5',
      'grade_point' => 'nullable|numeric|min:0|max:10',
      'remarks' => 'nullable|string|max:500',
    ];

    if ($request->credit_type === 'earned') {
      $rules['exam_subject_id'] = 'required|exists:exam_subject_masters,id';
    } else {
      $rules['source_institution'] = 'required|string|max:255';
      $rules['source_subject_code'] = 'nullable|string|max:50';
      $rules['source_subject_name'] = 'required|string|max:255';
      $rules['transfer_date'] = 'required|date';
      $rules['transfer_reference'] = 'nullable|string|max:255';
      $rules['exam_subject_id'] = 'nullable|exists:exam_subject_masters,id';
    }

    $validated = $request->validate($rules);

    // Set initial status
    if ($request->credit_type === 'transferred') {
      $validated['status'] = 'under_review';
    } else {
      $validated['status'] = 'active';
    }

    StudentCredit::create($validated);

    return redirect()->route('admin.student-credits.index')
      ->with('success', ucfirst($request->credit_type) . ' credit entry created successfully.');
  }

  public function show($id)
  {
    $credit = StudentCredit::with(['student', 'subject', 'verifier'])->findOrFail($id);

    // Get all credits for the same student for summary
    $studentCredits = StudentCredit::with('subject')
      ->where('exam_student_id', $credit->exam_student_id)
      ->orderBy('semester')
      ->orderBy('credit_type')
      ->get();

    $totalEarned = $studentCredits->where('credit_type', 'earned')->sum('credits_earned');
    $totalTransferred = $studentCredits->where('credit_type', 'transferred')
      ->whereIn('status', ['active', 'verified'])->sum('credits_earned');
    $grandTotal = $totalEarned + $totalTransferred;

    return view('coe.student-credits.show', compact('credit', 'studentCredits', 'totalEarned', 'totalTransferred', 'grandTotal'));
  }

  public function edit($id)
  {
    $credit = StudentCredit::findOrFail($id);
    $students = Student::orderBy('enrollment_no')->get();
    $subjects = ExamSubjectMaster::orderBy('subject_code')->get();

    return view('coe.student-credits.edit', compact('credit', 'students', 'subjects'));
  }

  public function update(Request $request, $id)
  {
    $credit = StudentCredit::findOrFail($id);

    $rules = [
      'credits_earned' => 'required|numeric|min:0.5|max:30',
      'semester' => 'required|integer|min:1|max:12',
      'grade' => 'nullable|string|max:5',
      'grade_point' => 'nullable|numeric|min:0|max:10',
      'remarks' => 'nullable|string|max:500',
    ];

    if ($credit->isEarned()) {
      $rules['exam_subject_id'] = 'required|exists:exam_subject_masters,id';
    } else {
      $rules['source_institution'] = 'required|string|max:255';
      $rules['source_subject_code'] = 'nullable|string|max:50';
      $rules['source_subject_name'] = 'required|string|max:255';
      $rules['transfer_date'] = 'required|date';
      $rules['transfer_reference'] = 'nullable|string|max:255';
      $rules['exam_subject_id'] = 'nullable|exists:exam_subject_masters,id';
    }

    $validated = $request->validate($rules);
    $credit->update($validated);

    return redirect()->route('admin.student-credits.show', $credit->id)
      ->with('success', 'Credit entry updated successfully.');
  }

  public function verify(Request $request, $id)
  {
    $credit = StudentCredit::findOrFail($id);

    if (!$credit->isTransferred()) {
      return back()->with('error', 'Only transferred credits can be verified.');
    }

    $credit->update([
      'status' => 'verified',
      'verified_by' => auth()->id(),
      'verified_at' => now(),
    ]);

    return back()->with('success', 'Transferred credit verified successfully.');
  }

  public function reject(Request $request, $id)
  {
    $credit = StudentCredit::findOrFail($id);

    if (!$credit->isTransferred()) {
      return back()->with('error', 'Only transferred credits can be rejected.');
    }

    $request->validate(['remarks' => 'required|string|max:500']);

    $credit->update([
      'status' => 'rejected',
      'verified_by' => auth()->id(),
      'verified_at' => now(),
      'remarks' => $request->remarks,
    ]);

    return back()->with('success', 'Transferred credit rejected.');
  }

  public function transcript($studentId)
  {
    $student = Student::findOrFail($studentId);
    $credits = StudentCredit::with('subject')
      ->where('exam_student_id', $studentId)
      ->whereIn('status', ['active', 'verified'])
      ->orderBy('semester')
      ->orderBy('credit_type')
      ->get();

    $semesterCredits = $credits->groupBy('semester');
    $totalEarned = $credits->where('credit_type', 'earned')->sum('credits_earned');
    $totalTransferred = $credits->where('credit_type', 'transferred')->sum('credits_earned');

    return view('coe.student-credits.transcript', compact('student', 'credits', 'semesterCredits', 'totalEarned', 'totalTransferred'));
  }

  public function export(Request $request)
  {
    $query = StudentCredit::with(['student', 'subject']);

    if ($request->filled('student_id')) {
      $query->where('exam_student_id', $request->student_id);
    }

    if ($request->filled('credit_type')) {
      $query->where('credit_type', $request->credit_type);
    }

    $credits = $query->orderBy('exam_student_id')->orderBy('semester')->get();
    return response()->json($credits);
  }
}
