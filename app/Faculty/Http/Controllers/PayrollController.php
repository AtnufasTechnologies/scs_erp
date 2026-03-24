<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StaticController;
use App\Models\Faculty;
use App\Models\FacultySalarySlip;
use App\Models\SubjectFacultyMaster;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollController extends Controller
{
  /**
   * Display a listing of salary slips
   */
  public function index(Request $request)
  {
    $facultyId = $this->getFacultyId();

    // Get filter parameters
    $year = $request->get('year', Carbon::now()->year);
    $month = $request->get('month');
    $status = $request->get('status');

    // Build query
    $query = FacultySalarySlip::where('faculty_id', $facultyId)
      ->orderBy('year', 'desc')
      ->orderBy('month', 'desc');

    if ($year) {
      $query->where('year', $year);
    }

    if ($month) {
      $query->where('month', $month);
    }

    if ($status) {
      $query->where('status', $status);
    }

    $salarySlips = $query->paginate(12);

    // Get available years for filter
    $availableYears = FacultySalarySlip::where('faculty_id', $facultyId)
      ->distinct()
      ->pluck('year')
      ->sort()
      ->reverse();

    // Get current year statistics
    $currentYearStats = [
      'total_paid' => FacultySalarySlip::where('faculty_id', $facultyId)
        ->where('year', Carbon::now()->year)
        ->paid()
        ->sum('net_salary'),
      'slips_count' => FacultySalarySlip::where('faculty_id', $facultyId)
        ->where('year', Carbon::now()->year)
        ->count(),
      'pending_count' => FacultySalarySlip::where('faculty_id', $facultyId)
        ->where('year', Carbon::now()->year)
        ->draft()
        ->count(),
    ];

    // Get faculty information
    $faculty = Faculty::find($facultyId);

    return view('faculty.payroll.index', compact(
      'salarySlips',
      'availableYears',
      'currentYearStats',
      'faculty',
      'year',
      'month',
      'status'
    ));
  }

  /**
   * Display the specified salary slip
   */
  public function show($id)
  {
    $facultyId = $this->getFacultyId();

    $salarySlip = FacultySalarySlip::where('faculty_id', $facultyId)
      ->with(['faculty', 'approver'])
      ->findOrFail($id);

    return view('faculty.payroll.show', compact('salarySlip'));
  }

  /**
   * Download salary slip as PDF
   */
  public function download($id)
  {
    $facultyId = $this->getFacultyId();

    $salarySlip = FacultySalarySlip::where('faculty_id', $facultyId)
      ->with(['faculty', 'approver'])
      ->findOrFail($id);

    $pdf = Pdf::loadView('faculty.payroll.pdf', compact('salarySlip'))
      ->setPaper('a4', 'portrait');

    $fileName = 'salary-slip-' . $salarySlip->salary_slip_number . '.pdf';

    return $pdf->download($fileName);
  }

  /**
   * Download multiple salary slips as PDF
   */
  public function downloadBulk(Request $request)
  {
    $facultyId = $this->getFacultyId();
    $year = $request->get('year', Carbon::now()->year);

    $salarySlips = FacultySalarySlip::where('faculty_id', $facultyId)
      ->where('year', $year)
      ->with(['faculty', 'approver'])
      ->orderBy('month')
      ->get();

    if ($salarySlips->isEmpty()) {
      return redirect()->back()->with('error', 'No salary slips found for the selected year.');
    }

    $pdf = Pdf::loadView('faculty.payroll.bulk-pdf', compact('salarySlips', 'year'))
      ->setPaper('a4', 'portrait');

    $fileName = 'salary-slips-' . $year . '.pdf';

    return $pdf->download($fileName);
  }

  /**
   * Get faculty ID from authenticated user
   */
  private function getFacultyId()
  {
    $userId = Auth::user()->id;
    return SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
  }
}
