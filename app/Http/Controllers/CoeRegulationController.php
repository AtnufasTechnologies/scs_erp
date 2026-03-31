<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamSystem\ProgramRegulation;
use App\Models\ExamSystem\Program;
use App\Models\ExamSystem\Exam;
use Illuminate\Support\Facades\DB;

class CoeRegulationController extends Controller
{
  /**
   * Display a listing of regulations
   */
  public function index(Request $request)
  {
    $query = ProgramRegulation::with('program');

    // Apply filters
    if ($request->filled('regulation_type')) {
      $query->where('regulation_type', $request->regulation_type);
    }

    if ($request->filled('program_id')) {
      $query->where('program_id', $request->program_id);
    }

    if ($request->filled('year')) {
      $year = $request->year;
      $query->where(function ($q) use ($year) {
        $q->where('start_year', '<=', $year)
          ->where('end_year', '>=', $year);
      });
    }

    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('regulation_name', 'like', "%{$search}%")
          ->orWhere('regulation_type', 'like', "%{$search}%");
      });
    }

    // Add exams count
    $query->withCount(['exams']);

    $regulations = $query->orderBy('start_year', 'desc')->paginate(15)->withQueryString();

    // Calculate statistics
    $totalRegulations = ProgramRegulation::count();
    $activeRegulations = ProgramRegulation::where('end_year', '>=', date('Y'))->count();

    // Count by program type
    $ugCount = ProgramRegulation::whereHas('program', function ($q) {
      $q->where('type', 'UG');
    })->count();

    $pgCount = ProgramRegulation::whereHas('program', function ($q) {
      $q->where('type', 'PG');
    })->count();

    // Load programs for filter dropdown
    $programs = Program::orderBy('name')->get();

    return view('coe.regulations.index', compact(
      'regulations',
      'totalRegulations',
      'activeRegulations',
      'ugCount',
      'pgCount',
      'programs'
    ));
  }

  /**
   * Show the form for creating a new regulation
   */
  public function create()
  {
    $programs = Program::orderBy('name')->get();

    return view('coe.regulations.create', compact('programs'));
  }

  /**
   * Store a newly created regulation
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'regulation_name' => 'required|string|max:255',
      'regulation_type' => 'required|string|in:Annual,Semester,Choice Based',
      'program_id' => 'required|exists:programs,id',
      'start_year' => 'required|integer|min:2000|max:2100',
      'end_year' => 'required|integer|min:2000|max:2100|gte:start_year'
    ]);

    try {
      $regulation = ProgramRegulation::create($validated);

      return redirect()
        ->route('coe.regulations.show', $regulation->id)
        ->with('success', 'Regulation created successfully!');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Failed to create regulation: ' . $e->getMessage());
    }
  }

  /**
   * Display the specified regulation
   */
  public function show($id)
  {
    $regulation = ProgramRegulation::with('program')->findOrFail($id);

    // Get associated exams
    $exams = Exam::where('regulation_id', $id)
      ->orderBy('start_date', 'desc')
      ->get();

    return view('coe.regulations.show', compact('regulation', 'exams'));
  }

  /**
   * Show the form for editing the specified regulation
   */
  public function edit($id)
  {
    $regulation = ProgramRegulation::findOrFail($id);
    $programs = Program::orderBy('name')->get();

    return view('coe.regulations.edit', compact('regulation', 'programs'));
  }

  /**
   * Update the specified regulation
   */
  public function update(Request $request, $id)
  {
    $regulation = ProgramRegulation::findOrFail($id);

    $validated = $request->validate([
      'regulation_name' => 'required|string|max:255',
      'regulation_type' => 'required|string|in:Annual,Semester,Choice Based',
      'program_id' => 'required|exists:programs,id',
      'start_year' => 'required|integer|min:2000|max:2100',
      'end_year' => 'required|integer|min:2000|max:2100|gte:start_year'
    ]);

    try {
      $regulation->update($validated);

      return redirect()
        ->route('coe.regulations.show', $regulation->id)
        ->with('success', 'Regulation updated successfully!');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Failed to update regulation: ' . $e->getMessage());
    }
  }

  /**
   * Remove the specified regulation
   */
  public function destroy($id)
  {
    try {
      $regulation = ProgramRegulation::findOrFail($id);

      // Check if there are any associated exams
      $examCount = Exam::where('regulation_id', $id)->count();

      if ($examCount > 0) {
        return redirect()
          ->back()
          ->with('error', 'Cannot delete regulation with associated exams. Please delete or reassign exams first.');
      }

      $regulationName = $regulation->regulation_name;
      $regulation->delete();

      return redirect()
        ->route('coe.regulations.index')
        ->with('success', "Regulation '{$regulationName}' deleted successfully!");
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Failed to delete regulation: ' . $e->getMessage());
    }
  }
}
