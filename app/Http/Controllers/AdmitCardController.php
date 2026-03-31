<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\ExamSession;
use App\Models\ExamSystem\Registration;
use App\Models\StudentMaster;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Http\Request;
use PDF;

class AdmitCardController extends Controller
{
  public function index(Request $request)
  {
    $query = Registration::with(['examSession', 'student', 'seatingAllocation', 'dummyNumber'])
      ->where('status', 'approved');

    if ($request->has('exam_session_id') && $request->exam_session_id != '') {
      $query->where('exam_session_id', $request->exam_session_id);
    }

    if ($request->has('search') && $request->search != '') {
      $search = $request->search;
      $query->whereHas('student', function ($q) use ($search) {
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('register_no', 'like', "%{$search}%")
          ->orWhere('roll_no', 'like', "%{$search}%");
      });
    }

    $registrations = $query->paginate(50);
    $examSessions = ExamSession::orderBy('start_date', 'desc')->get();

    return view('coe.admit-cards.index', compact('registrations', 'examSessions'));
  }

  public function show($id)
  {
    $registration = Registration::with([
      'examSession',
      'student.programgroup.programInfo',
      'student.deptmaster',
      'seatingAllocation',
      'dummyNumber',
      'subjects'
    ])->findOrFail($id);

    return view('coe.admit-cards.show', compact('registration'));
  }

  public function downloadPdf($id)
  {
    $registration = Registration::with([
      'examSession',
      'student.programgroup.programInfo',
      'student.deptmaster',
      'seatingAllocation',
      'dummyNumber',
      'subjects'
    ])->findOrFail($id);

    if (!$registration->seatingAllocation || !$registration->dummyNumber) {
      return redirect()->back()->with('error', 'Cannot generate admit card. Seating or dummy number not assigned.');
    }

    $pdf = FacadePdf::loadView('coe.admit-cards.pdf', compact('registration'));

    $filename = 'admit_card_' . ($registration->student->register_no ?? $registration->id) . '.pdf';
    return $pdf->download($filename);
  }

  public function generate(Request $request)
  {
    $examSessions = ExamSession::orderBy('start_date', 'desc')->get();
    $programmes = \App\Models\StudentProgram::all();
    $departments = \App\Models\Department::all();

    return view('coe.admit-cards.generate', compact('examSessions', 'programmes', 'departments'));
  }

  public function bulkDownload(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|exists:exam_sessions,id',
    ]);

    $query = Registration::with([
      'examSession',
      'student.programgroup.programInfo',
      'student.deptmaster',
      'seatingAllocation',
      'dummyNumber',
      'subjects'
    ])
      ->where('exam_session_id', $request->exam_session_id)
      ->where('status', 'approved')
      ->whereHas('seatingAllocation')
      ->whereHas('dummyNumber');

    if ($request->filled('programme_id')) {
      $query->whereHas('student', function ($q) use ($request) {
        $q->where('programme', $request->programme_id);
      });
    }

    if ($request->filled('department_id')) {
      $query->whereHas('student', function ($q) use ($request) {
        $q->where('department', $request->department_id);
      });
    }

    $registrations = $query->get();

    if ($registrations->isEmpty()) {
      return redirect()->back()->with('error', 'No students found matching the criteria with complete information.');
    }

    $pdf = FacadePdf::loadView('coe.admit-cards.bulk-pdf', compact('registrations'));

    $filename = 'admit_cards_bulk_' . date('Y-m-d_His') . '.pdf';
    return $pdf->download($filename);
  }
}
