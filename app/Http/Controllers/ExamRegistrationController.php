<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\ExamSession;
use App\Models\ExamSystem\Registration;
use App\Models\StudentMaster;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExamRegistrationController extends Controller
{
  /**
   * Display a listing of exam registrations
   */
  public function index(Request $request)
  {
    $query = Registration::with([
      'student:id,first_name,last_name,register_no,roll_no,campus_id',
      'student.campusmaster:id,name',
      'student.batchmaster:id,batch_name',
      'examSession:id,name,academic_year,semester,program_type,start_date,end_date'
    ]);

    // Apply filters
    if ($request->has('exam_session_id') && $request->exam_session_id != '') {
      $query->where('exam_session_id', $request->exam_session_id);
    }

    if ($request->has('status') && $request->status != '') {
      $query->where('status', $request->status);
    }

    if ($request->has('campus_id') && $request->campus_id != '') {
      $query->whereHas('student', function ($q) use ($request) {
        $q->where('campus_id', $request->campus_id);
      });
    }

    if ($request->has('is_backlog')) {
      $query->where('is_backlog', $request->is_backlog);
    }

    if ($request->has('search') && $request->search != '') {
      $search = $request->search;
      $query->whereHas('student', function ($q) use ($search) {
        $q->where('first_name', 'LIKE', "%{$search}%")
          ->orWhere('last_name', 'LIKE', "%{$search}%")
          ->orWhere('register_no', 'LIKE', "%{$search}%")
          ->orWhere('roll_no', 'LIKE', "%{$search}%");
      });
    }

    $registrations = $query->orderBy('created_at', 'desc')->paginate(50);

    // Fetch filter data
    $examSessions = ExamSession::select('id', 'name', 'academic_year', 'semester', 'program_type')
      ->orderBy('start_date', 'desc')->get();
    $campuses = Campus::all();

    return view('coe.exam-registrations.index', compact('registrations', 'examSessions', 'campuses'));
  }

  /**
   * Show the form for creating a new registration
   */
  public function create()
  {
    $exams = Exam::where('status', 'active')->orderBy('exam_date', 'desc')->get();
    $students = StudentMaster::where('is_deleted', 0)
      ->where('is_left', 0)
      ->orderBy('first_name')
      ->get();
    $semesters = Semester::all();

    return view('coe.exam-registrations.create', compact('exams', 'students', 'semesters'));
  }

  /**
   * Store a newly created registration
   */
  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'exam_id' => 'required|exists:exams,id',
      'exam_student_id' => 'required|exists:student_masters,id',
      'semester_id' => 'nullable|exists:semesters,id',
      'registration_fee' => 'nullable|numeric|min:0',
      'is_backlog' => 'boolean',
      'is_regular' => 'boolean',
      'status' => 'required|in:pending,approved,rejected,cancelled',
    ]);

    if ($validator->fails()) {
      return redirect()->back()
        ->withErrors($validator)
        ->withInput();
    }

    // Check for duplicate registration
    $exists = Registration::where('exam_id', $request->exam_id)
      ->where('exam_student_id', $request->exam_student_id)
      ->exists();

    if ($exists) {
      return redirect()->back()
        ->with('error', 'Student is already registered for this exam')
        ->withInput();
    }

    try {
      DB::beginTransaction();

      $registration = new Registration();
      $registration->exam_id = $request->exam_id;
      $registration->exam_student_id = $request->exam_student_id;
      $registration->semester_id = $request->semester_id;
      $registration->is_backlog = $request->has('is_backlog') ? 1 : 0;
      $registration->is_regular = $request->has('is_regular') ? 1 : 0;
      $registration->registration_fee = $request->registration_fee ?? 0;
      $registration->registration_date = now();
      $registration->status = $request->status ?? 'pending';
      $registration->remarks = $request->remarks;

      // Generate registration number
      $registration->registration_number = $this->generateRegistrationNumber();

      if ($request->status == 'approved') {
        $registration->approved_by = Auth::id();
        $registration->approved_at = now();
      }

      $registration->save();

      DB::commit();

      return redirect()->route('admin.exam-registrations.index')
        ->with('success', 'Exam registration created successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Failed to create registration: ' . $e->getMessage())
        ->withInput();
    }
  }

  /**
   * Display the specified registration
   */
  public function show($id)
  {
    $registration = Registration::with([
      'student.campusmaster',
      'student.batchmaster',
      'student.deptmaster',
      'exam',
      'exam.program'
    ])->findOrFail($id);

    return view('coe.exam-registrations.show', compact('registration'));
  }

  /**
   * Show the form for editing the specified registration
   */
  public function edit($id)
  {
    $registration = Registration::findOrFail($id);
    $exams = Exam::orderBy('exam_date', 'desc')->get();
    $students = StudentMaster::where('is_deleted', 0)
      ->where('is_left', 0)
      ->orderBy('first_name')
      ->get();
    $semesters = Semester::all();

    return view('coe.exam-registrations.edit', compact('registration', 'exams', 'students', 'semesters'));
  }

  /**
   * Update the specified registration
   */
  public function update(Request $request, $id)
  {
    $registration = Registration::findOrFail($id);

    $validator = Validator::make($request->all(), [
      'exam_id' => 'required|exists:exams,id',
      'semester_id' => 'nullable|exists:semesters,id',
      'registration_fee' => 'nullable|numeric|min:0',
      'is_backlog' => 'boolean',
      'is_regular' => 'boolean',
      'status' => 'required|in:pending,approved,rejected,cancelled',
    ]);

    if ($validator->fails()) {
      return redirect()->back()
        ->withErrors($validator)
        ->withInput();
    }

    try {
      DB::beginTransaction();

      $registration->exam_id = $request->exam_id;
      $registration->semester_id = $request->semester_id;
      $registration->is_backlog = $request->has('is_backlog') ? 1 : 0;
      $registration->is_regular = $request->has('is_regular') ? 1 : 0;
      $registration->registration_fee = $request->registration_fee ?? 0;
      $registration->fee_paid = $request->has('fee_paid') ? 1 : 0;
      $registration->payment_reference = $request->payment_reference;
      $registration->status = $request->status;
      $registration->remarks = $request->remarks;

      if ($request->status == 'approved' && !$registration->approved_at) {
        $registration->approved_by = Auth::id();
        $registration->approved_at = now();
      }

      $registration->save();

      DB::commit();

      return redirect()->route('admin.exam-registrations.index')
        ->with('success', 'Exam registration updated successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Failed to update registration: ' . $e->getMessage())
        ->withInput();
    }
  }

  /**
   * Remove the specified registration
   */
  public function destroy($id)
  {
    try {
      $registration = Registration::findOrFail($id);
      $registration->delete();

      return redirect()->route('admin.exam-registrations.index')
        ->with('success', 'Exam registration deleted successfully');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to delete registration: ' . $e->getMessage());
    }
  }

  /**
   * Bulk approve registrations
   */
  public function bulkApprove(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'registration_ids' => 'required|array',
      'registration_ids.*' => 'exists:exam_registrations,id'
    ]);

    if ($validator->fails()) {
      return redirect()->back()->with('error', 'Invalid registration IDs');
    }

    try {
      Registration::whereIn('id', $request->registration_ids)
        ->update([
          'status' => 'approved',
          'approved_by' => Auth::id(),
          'approved_at' => now()
        ]);

      return redirect()->back()
        ->with('success', count($request->registration_ids) . ' registrations approved successfully');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to approve registrations: ' . $e->getMessage());
    }
  }

  /**
   * Bulk reject registrations
   */
  public function bulkReject(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'registration_ids' => 'required|array',
      'registration_ids.*' => 'exists:exam_registrations,id'
    ]);

    if ($validator->fails()) {
      return redirect()->back()->with('error', 'Invalid registration IDs');
    }

    try {
      Registration::whereIn('id', $request->registration_ids)
        ->update(['status' => 'rejected']);

      return redirect()->back()
        ->with('success', count($request->registration_ids) . ' registrations rejected successfully');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to reject registrations: ' . $e->getMessage());
    }
  }

  /**
   * API endpoint for student registration
   */
  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'exam_id' => 'required|exists:exams,id',
      'student_id' => 'required|exists:student_masters,id',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $validator->errors()
      ], 422);
    }

    // Check if already registered
    $exists = Registration::where('exam_id', $request->exam_id)
      ->where('exam_student_id', $request->student_id)
      ->exists();

    if ($exists) {
      return response()->json([
        'success' => false,
        'message' => 'Already registered for this exam'
      ], 400);
    }

    try {
      $registration = Registration::create([
        'exam_id' => $request->exam_id,
        'exam_student_id' => $request->student_id,
        'registration_number' => $this->generateRegistrationNumber(),
        'registration_date' => now(),
        'status' => 'pending'
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Registration successful',
        'data' => $registration
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Registration failed',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Generate unique registration number
   */
  private function generateRegistrationNumber()
  {
    $year = now()->format('Y');
    $count = Registration::whereYear('created_at', $year)->count() + 1;
    return 'EXAMREG-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
  }

  /**
   * Export registrations to Excel/PDF
   */
  public function export(Request $request)
  {
    // Implementation for export functionality
    // Can use Maatwebsite Excel package
    return redirect()->back()->with('info', 'Export functionality coming soon');
  }
}
