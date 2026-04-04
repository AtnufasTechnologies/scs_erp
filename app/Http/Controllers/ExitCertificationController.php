<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\ExitCertification;
use App\Models\ExamSystem\Student;
use App\Services\ExamSystem\ExitCertificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExitCertificationController extends Controller
{
  protected ExitCertificationService $service;

  public function __construct(ExitCertificationService $service)
  {
    $this->service = $service;
  }

  public function index(Request $request)
  {
    $query = ExitCertification::with(['student', 'program']);

    if ($request->filled('exit_level')) {
      $query->where('exit_level', $request->exit_level);
    }
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }
    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('certificate_no', 'like', "%{$search}%")
          ->orWhereHas('student', function ($sq) use ($search) {
            $sq->where('enrollment_no', 'like', "%{$search}%");
          });
      });
    }

    $records = $query->orderBy('created_at', 'desc')->paginate(50);

    // Stats
    $totalRecords = ExitCertification::count();
    $pendingCount = ExitCertification::where('status', 'pending')->count();
    $issuedCount = ExitCertification::where('status', 'issued')->count();
    $levelCounts = ExitCertification::select('exit_level', DB::raw('count(*) as count'))
      ->groupBy('exit_level')
      ->pluck('count', 'exit_level')
      ->toArray();

    return view('coe.exit-certification.index', compact(
      'records',
      'totalRecords',
      'pendingCount',
      'issuedCount',
      'levelCounts'
    ));
  }

  public function create(Request $request)
  {
    $students = Student::orderBy('enrollment_no')->get();
    $eligibility = null;
    $selectedStudent = null;

    if ($request->filled('exam_student_id')) {
      $selectedStudent = Student::findOrFail($request->exam_student_id);
      $eligibility = $this->service->checkEligibility($request->exam_student_id);
    }

    return view('coe.exit-certification.create', compact('students', 'eligibility', 'selectedStudent'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_student_id' => 'required|exists:exam_students,id',
      'exit_level' => 'required|in:certificate,diploma,degree,honors',
      'remarks' => 'nullable|string|max:1000',
    ]);

    try {
      $certification = $this->service->issueCertification(
        $request->exam_student_id,
        $request->exit_level,
        $request->remarks
      );

      return redirect()->route('admin.exit-certification.index')
        ->with('success', "Exit certification created: {$certification->certificate_no}");
    } catch (\Exception $e) {
      return redirect()->back()->withInput()
        ->with('error', $e->getMessage());
    }
  }

  public function show($id)
  {
    $record = ExitCertification::with(['student', 'program', 'approver', 'issuer'])->findOrFail($id);
    return view('coe.exit-certification.show', compact('record'));
  }

  public function approve($id)
  {
    $record = ExitCertification::findOrFail($id);

    if ($record->status !== 'pending') {
      return redirect()->back()->with('error', 'Only pending certifications can be approved.');
    }

    $record->update([
      'status' => 'approved',
      'approved_by' => auth()->id(),
    ]);

    return redirect()->back()->with('success', 'Certification approved successfully.');
  }

  public function issue($id)
  {
    $record = ExitCertification::findOrFail($id);

    if ($record->status !== 'approved') {
      return redirect()->back()->with('error', 'Only approved certifications can be issued.');
    }

    $record->update([
      'status' => 'issued',
      'issue_date' => now(),
      'issued_by' => auth()->id(),
    ]);

    return redirect()->back()->with('success', 'Certification issued. Certificate can now be downloaded.');
  }

  public function revoke($id)
  {
    $record = ExitCertification::findOrFail($id);

    if ($record->status === 'revoked') {
      return redirect()->back()->with('error', 'Already revoked.');
    }

    $record->update(['status' => 'revoked']);

    return redirect()->back()->with('success', 'Certification revoked.');
  }

  public function downloadCertificate($id)
  {
    $record = ExitCertification::with(['student', 'program'])->findOrFail($id);

    if ($record->status !== 'issued') {
      return redirect()->back()->with('error', 'Certificate can only be downloaded after being issued.');
    }

    $pdf = Pdf::loadView('coe.exit-certification.certificate-pdf', compact('record'))
      ->setPaper('a4', 'landscape');

    return $pdf->download("exit-certificate-{$record->certificate_no}.pdf");
  }

  public function destroy($id)
  {
    $record = ExitCertification::findOrFail($id);

    if ($record->status === 'issued') {
      return redirect()->back()->with('error', 'Cannot delete issued certifications. Revoke first.');
    }

    $record->delete();

    return redirect()->route('admin.exit-certification.index')
      ->with('success', 'Certification record deleted.');
  }
}
