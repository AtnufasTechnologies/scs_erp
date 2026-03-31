<?php

namespace App\Http\Controllers;

use App\Models\FacultyRemuneration;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamRemunerationController extends Controller
{
  public function index(Request $request)
  {
    $query = FacultyRemuneration::with(['faculty']);

    if ($request->has('faculty_id') && $request->faculty_id != '') {
      $query->where('faculty_id', $request->faculty_id);
    }

    if ($request->has('type') && $request->type != '') {
      $query->where('type', $request->type);
    }

    if ($request->has('status') && $request->status != '') {
      $query->where('status', $request->status);
    }

    $remunerations = $query->orderBy('created_at', 'desc')->paginate(50);
    $faculties = Faculty::all();

    return view('coe.remuneration.index', compact('remunerations', 'faculties'));
  }

  public function create()
  {
    $faculties = Faculty::all();

    return view('coe.remuneration.create', compact('faculties'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'faculty_id' => 'required|exists:faculties,id',
      'type' => 'required|in:invigilation,evaluation,moderation,other',
      'amount' => 'required|numeric|min:0',
      'description' => 'nullable|string',
    ]);

    FacultyRemuneration::create(array_merge($request->all(), ['status' => 'pending']));

    return redirect()->route('coe.remuneration.index')
      ->with('success', 'Remuneration entry created successfully');
  }

  public function show($id)
  {
    $remuneration = FacultyRemuneration::with(['faculty'])->findOrFail($id);
    return view('coe.remuneration.show', compact('remuneration'));
  }

  public function edit($id)
  {
    $remuneration = FacultyRemuneration::findOrFail($id);
    $faculties = Faculty::all();

    return view('coe.remuneration.edit', compact('remuneration', 'faculties'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'faculty_id' => 'required|exists:faculties,id',
      'type' => 'required|in:invigilation,evaluation,moderation,other',
      'amount' => 'required|numeric|min:0',
      'description' => 'nullable|string',
      'status' => 'nullable|in:pending,approved,paid',
    ]);

    $remuneration = FacultyRemuneration::findOrFail($id);
    $remuneration->update($request->all());

    return redirect()->route('coe.remuneration.index')
      ->with('success', 'Remuneration updated successfully');
  }

  public function destroy($id)
  {
    $remuneration = FacultyRemuneration::findOrFail($id);
    $remuneration->delete();

    return redirect()->route('coe.remuneration.index')
      ->with('success', 'Remuneration deleted successfully');
  }

  public function approve($id)
  {
    $remuneration = FacultyRemuneration::findOrFail($id);
    $remuneration->update(['status' => 'approved']);

    return redirect()->back()->with('success', 'Remuneration approved');
  }

  public function markPaid($id)
  {
    $remuneration = FacultyRemuneration::findOrFail($id);
    $remuneration->update(['status' => 'paid', 'paid_at' => now()]);

    return redirect()->back()->with('success', 'Remuneration marked as paid');
  }

  public function export(Request $request)
  {
    $query = FacultyRemuneration::with(['faculty', 'exam']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    $remunerations = $query->get();
    return response()->json($remunerations);
  }

  public function autoCalculate(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
    ]);

    try {
      DB::beginTransaction();

      // Auto-calculate remuneration logic
      // Get all duties (invigilation, evaluation, moderation) for the exam
      // Apply rates based on duty type and duration
      // Calculate total amount for each faculty
      // Create remuneration records

      DB::commit();
      return redirect()->route('admin.exam-remuneration.index')
        ->with('success', 'Remuneration auto-calculated successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Auto-calculation failed: ' . $e->getMessage());
    }
  }
}
