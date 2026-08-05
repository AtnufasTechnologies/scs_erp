<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\DsaConcernCategory;
use App\Models\DsaCounsellingCase;
use App\Models\StudentMaster;
use App\Services\Dean\CampusContextService;
use Illuminate\Http\Request;

class CounsellingController extends Controller
{
  public function __construct(protected CampusContextService $campusContext) {}

  public function index()
  {
    $casesQuery = DsaCounsellingCase::with('student:id,first_name,last_name,roll_no')->latest();
    $this->campusContext->applyStudentRelationCampus($casesQuery, 'student');
    $cases = $casesQuery->paginate(25);

    $studentsQuery = StudentMaster::select('id', 'first_name', 'last_name', 'roll_no')->orderBy('first_name')->limit(1000);
    $this->campusContext->applyStudentCampus($studentsQuery);
    $students = $studentsQuery->get();

    $concernCategories = DsaConcernCategory::where('is_active', true)
      ->orderBy('sort_order')
      ->orderBy('name')
      ->get(['id', 'name']);

    return view('student-affairs.counselling.index', compact('cases', 'students', 'concernCategories'));
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'student_id' => 'required|exists:student_masters,id',
      'summary' => 'required|string|max:500',
      'risk_level' => 'required|in:low,medium,high,critical',
      'referral_source' => 'required|string|max:40',
      'concern_category_id' => 'nullable|exists:dsa_concern_categories,id',
      'referred_on' => 'nullable|date',
    ]);

    $validated['case_no'] = 'COUN-' . now()->format('YmdHis') . '-' . random_int(100, 999);
    $validated['status'] = 'open';
    $validated['created_by'] = auth()->id();

    $allowedStudent = StudentMaster::where('id', (int) $validated['student_id']);
    $this->campusContext->applyStudentCampus($allowedStudent);
    if (!$allowedStudent->exists()) {
      abort(403, 'Selected student is outside your assigned campus.');
    }

    if (!empty($validated['concern_category_id'])) {
      $concernCategory = DsaConcernCategory::where('id', (int) $validated['concern_category_id'])
        ->where('is_active', true)
        ->firstOrFail();
      $validated['concern_category'] = $concernCategory->name;
    }

    DsaCounsellingCase::create($validated);

    return back()->with('success', 'Counselling case created.');
  }
}
