<?php

namespace App\Http\Controllers;

use App\Models\FacultyRemuneration;
use App\Models\ExamSystem\FacultyProfile;
use App\Models\ExamSystem\InvigilationDuty;
use App\Models\ExamSystem\EvaluationDuty;
use App\Models\ExamSystem\ModerationDuty;
use App\Services\RemunerationService;
use App\Exports\FacultyRemunerationExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExamRemunerationController extends Controller
{
  public function index(Request $request)
  {
    $query = FacultyRemuneration::with(['faculty']);

    if ($request->filled('faculty_id')) {
      $query->where('faculty_id', $request->faculty_id);
    }

    if ($request->filled('duty_type')) {
      $query->where('duty_type', $request->duty_type);
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    $remunerations = $query->orderBy('created_at', 'desc')->paginate(50);
    $faculties = FacultyProfile::orderBy('name')->get();

    // Statistics
    $totalAmount = FacultyRemuneration::sum('total_amount');
    $pendingAmount = FacultyRemuneration::pending()->sum('total_amount');
    $approvedAmount = FacultyRemuneration::approved()->sum('total_amount');
    $paidAmount = FacultyRemuneration::paid()->sum('total_amount');
    $pendingCount = FacultyRemuneration::pending()->count();
    $approvedCount = FacultyRemuneration::approved()->count();
    $paidCount = FacultyRemuneration::paid()->count();

    return view('coe.remuneration.index', compact(
      'remunerations',
      'faculties',
      'totalAmount',
      'pendingAmount',
      'approvedAmount',
      'paidAmount',
      'pendingCount',
      'approvedCount',
      'paidCount'
    ));
  }

  public function create()
  {
    $faculties = FacultyProfile::orderBy('name')->get();
    return view('coe.remuneration.create', compact('faculties'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'faculty_id' => 'required|exists:faculty_profiles,id',
      'duty_type' => 'required|in:invigilation,evaluation,moderation',
      'quantity' => 'required|integer|min:1',
      'rate' => 'required|numeric|min:0',
    ]);

    $totalAmount = $request->quantity * $request->rate;

    FacultyRemuneration::create([
      'faculty_id' => $request->faculty_id,
      'duty_type' => $request->duty_type,
      'quantity' => $request->quantity,
      'rate' => $request->rate,
      'total_amount' => $totalAmount,
      'status' => 'pending',
      'generated_at' => now(),
    ]);

    return redirect()->route('admin.exam-remuneration.index')
      ->with('success', 'Remuneration entry created successfully');
  }

  public function show($id)
  {
    $remuneration = FacultyRemuneration::with(['faculty'])->findOrFail($id);

    // Get faculty earnings summary
    $facultyEarnings = FacultyRemuneration::where('faculty_id', $remuneration->faculty_id)
      ->selectRaw('duty_type, SUM(total_amount) as total, COUNT(*) as count, status')
      ->groupBy('duty_type', 'status')
      ->get();

    return view('coe.remuneration.show', compact('remuneration', 'facultyEarnings'));
  }

  public function approve($id)
  {
    $remuneration = FacultyRemuneration::findOrFail($id);
    $remuneration->update(['status' => 'approved']);

    return redirect()->back()->with('success', 'Remuneration approved successfully');
  }

  public function markPaid($id)
  {
    $remuneration = FacultyRemuneration::findOrFail($id);
    $remuneration->update(['status' => 'paid']);

    return redirect()->back()->with('success', 'Remuneration marked as paid');
  }

  public function export(Request $request)
  {
    return Excel::download(new FacultyRemunerationExport($request->all()), 'remuneration_report.xlsx');
  }

  public function autoCalculate(Request $request)
  {
    try {
      DB::beginTransaction();
      $service = new RemunerationService();
      $count = 0;

      // Process completed invigilation duties without existing remuneration
      $invigilationDuties = InvigilationDuty::where('status', 'completed')
        ->whereNotIn('id', FacultyRemuneration::where('duty_type', 'invigilation')->pluck('reference_id'))
        ->get();

      foreach ($invigilationDuties as $duty) {
        $result = $service->generateRemuneration([
          'faculty_id' => $duty->faculty_id,
          'duty_type' => 'invigilation',
          'reference_id' => $duty->id,
          'quantity' => 1,
        ]);
        if ($result) $count++;
      }

      // Process completed evaluation duties without existing remuneration
      $evaluationDuties = EvaluationDuty::where('status', 'completed')
        ->whereNotIn('id', FacultyRemuneration::where('duty_type', 'evaluation')->pluck('reference_id'))
        ->get();

      foreach ($evaluationDuties as $duty) {
        $result = $service->generateRemuneration([
          'faculty_id' => $duty->faculty_id,
          'duty_type' => 'evaluation',
          'reference_id' => $duty->id,
          'quantity' => $duty->copies_evaluated ?? 1,
        ]);
        if ($result) $count++;
      }

      // Process completed moderation duties without existing remuneration
      $moderationDuties = ModerationDuty::where('status', 'completed')
        ->whereNotIn('id', FacultyRemuneration::where('duty_type', 'moderation')->pluck('reference_id'))
        ->get();

      foreach ($moderationDuties as $duty) {
        $result = $service->generateRemuneration([
          'faculty_id' => $duty->faculty_id,
          'duty_type' => 'moderation',
          'reference_id' => $duty->id,
          'quantity' => 1,
        ]);
        if ($result) $count++;
      }

      DB::commit();
      return redirect()->route('admin.exam-remuneration.index')
        ->with('success', "Remuneration auto-calculated successfully. {$count} new entries created.");
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Auto-calculation failed: ' . $e->getMessage());
    }
  }
}
