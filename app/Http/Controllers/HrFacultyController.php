<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\FacultySalaryMaster;
use App\Models\HrFacultyStatusHistory;
use App\Models\HrGradeLevel;
use App\Models\HrPayMatrix;
use App\Models\HrDesignation;
use App\Models\NationalityMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\SubjectFacultyMaster;

class HrFacultyController extends Controller
{
  /**
   * Display a listing of faculty members
   */
  public function index(Request $request)
  {
    $search = $request->get('search');
    $status = $request->get('status');

    $totalStaff = Faculty::count();
    $totalPayMatrices = HrPayMatrix::count();

    $query = Faculty::with(['nationality', 'salaryMaster.payMatrix']);

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('USER_CODE', 'like', "%$search%")
          ->orWhere('FIRST_NAME', 'like', "%$search%")
          ->orWhere('LAST_NAME', 'like', "%$search%")
          ->orWhere('MAIL_ID', 'like', "%$search%")
          ->orWhere('MOBILE_NO', 'like', "%$search%");
      });
    }

    if ($status === 'active') {
      $query->where('IS_LEFT', 0);
    } elseif ($status === 'left') {
      $query->where('IS_LEFT', 1);
    }

    $faculties = $query->latest()->paginate(20);

    return view('hr.faculty.index', compact('faculties', 'search', 'status', 'totalStaff', 'totalPayMatrices'));
  }

  /**
   * Show the form for creating a new faculty member
   */
  public function create()
  {
    $nationalities = NationalityMaster::orderBy('name')->get();
    return view('hr.faculty.create', compact('nationalities'));
  }

  /**
   * Store a newly created faculty member
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'USER_CODE' => 'required|string|max:50|unique:faculties,USER_CODE',
      'FIRST_NAME' => 'required|string|max:100',
      'MIDDLE_NAME' => 'nullable|string|max:100',
      'LAST_NAME' => 'nullable|string|max:100',
      'GENDER' => 'required|in:1,2,3',
      'MAIL_ID' => 'required|email|max:100|unique:faculties,MAIL_ID',
      'MOBILE_NO' => 'required|string|max:15',
      'ADDRESS' => 'nullable|string',
      'DOB' => 'nullable|date',
      'DOJ' => 'nullable|date',
      'reactivation_date' => 'nullable|date|after_or_equal:DOJ',
      'hr_remark' => 'nullable|string|max:1000',
      'NATIONALITY' => 'nullable|exists:nationality_masters,id',
      'employee_type' => 'nullable|string|max:50',
      'designation' => 'nullable|string|max:100',
      'qualification' => 'nullable|string|max:255',
      'specialization' => 'nullable|string|max:255',
      'experience_years' => 'nullable|integer|min:0',
      'pan_number' => 'nullable|string|max:20',
      'aadhar_number' => 'nullable|string|max:20',
      'bank_account_number' => 'nullable|string|max:50',
      'bank_ifsc_code' => 'nullable|string|max:20',
      'bank_name' => 'nullable|string|max:100',
      'emergency_contact_name' => 'nullable|string|max:100',
      'emergency_contact_number' => 'nullable|string|max:15',
      'permanent_address' => 'nullable|string',
      'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
      'user_type' => 'required|in:1,2',
      'CAMPUS_ID' => 'required|exists:campuses,id',
      'responsibility' => 'nullable|string|max:255',
      'paper_publications_count' => 'nullable|integer|min:0',
      'orcid_id' => 'nullable|string|max:50',
    ]);

    // Handle photo upload
    if ($request->hasFile('photo')) {
      $file = $request->file('photo');
      $validated['photo'] = StaticController::s3_file_uploader($file, 'faculty_photos');
    }

    $faculty = Faculty::create($validated);

    return redirect()->route('hr.faculty.index')
      ->with('success', 'Faculty member created successfully!');
  }

  /**
   * Display the specified faculty member
   */
  public function show($id)
  {
    $faculty = Faculty::with([
      'nationality',
      'statusHistories' => function ($q) {
        $q->latest('status_on')->latest('id');
      },
      'leaveApplications' => function ($q) {
        $q->orderBy('created_at', 'desc')->limit(10);
      },
      'fdpParticipations.fdpProgram',
      'salaryMaster'
    ])->findOrFail($id);

    // Calculate leave statistics
    $leaveStats = [
      'total_applied' => $faculty->leaveApplications()->count(),
      'approved' => $faculty->leaveApplications()->where('status', 'approved')->count(),
      'pending' => $faculty->leaveApplications()->where('status', 'pending')->count(),
      'rejected' => $faculty->leaveApplications()->where('status', 'rejected')->count(),
    ];

    // Calculate FDP statistics
    $fdpStats = [
      'total_participated' => $faculty->fdpParticipations()->count(),
      'completed' => $faculty->completedFdpPrograms()->count(),
      'ongoing' => $faculty->fdpParticipations()
        ->whereHas('fdpProgram', function ($q) {
          $q->where('status', 'ongoing');
        })->count(),
    ];

    return view('hr.faculty.show', compact('faculty', 'leaveStats', 'fdpStats'));
  }

  /**
   * Show the form for editing the specified faculty member
   */
  public function edit($id)
  {
    $faculty = Faculty::findOrFail($id);
    $nationalities = NationalityMaster::orderBy('name')->get();
    return view('hr.faculty.edit', compact('faculty', 'nationalities'));
  }

  /**
   * Update the specified faculty member
   */
  public function update(Request $request, $id)
  {
    $faculty = Faculty::findOrFail($id);

    $validated = $request->validate([
      'USER_CODE' => 'required|string|max:50|unique:faculties,USER_CODE,' . $id,
      'FIRST_NAME' => 'required|string|max:100',
      'MIDDLE_NAME' => 'nullable|string|max:100',
      'LAST_NAME' => 'nullable|string|max:100',
      'GENDER' => 'required|in:1,2,3',
      'MAIL_ID' => 'required|email|max:100|unique:faculties,MAIL_ID,' . $id,
      'MOBILE_NO' => 'required|string|max:15',
      'ADDRESS' => 'nullable|string',
      'DOB' => 'nullable|date',
      'DOJ' => 'nullable|date',
      'DOL' => 'nullable|date',
      'reactivation_date' => 'nullable|date|after_or_equal:DOJ',
      'hr_remark' => 'nullable|string|max:1000',
      'IS_LEFT' => 'nullable|boolean',
      'NATIONALITY' => 'nullable|exists:nationality_masters,id',
      'employee_type' => 'nullable|string|max:50',
      'designation' => 'nullable|string|max:100',
      'qualification' => 'nullable|string|max:255',
      'specialization' => 'nullable|string|max:255',
      'experience_years' => 'nullable|integer|min:0',
      'pan_number' => 'nullable|string|max:20',
      'aadhar_number' => 'nullable|string|max:20',
      'bank_account_number' => 'nullable|string|max:50',
      'bank_ifsc_code' => 'nullable|string|max:20',
      'bank_name' => 'nullable|string|max:100',
      'emergency_contact_name' => 'nullable|string|max:100',
      'emergency_contact_number' => 'nullable|string|max:15',
      'permanent_address' => 'nullable|string',
      'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
      'CAMPUS_ID' => 'required|exists:campuses,id',
      'responsibility' => 'nullable|string|max:255',
      'paper_publications_count' => 'nullable|integer|min:0',
      'orcid_id' => 'nullable|string|max:50',
    ]);

    // Handle photo upload
    if ($request->hasFile('photo')) {
      $file = $request->file('photo');
      $validated['photo'] = StaticController::s3_file_uploader($file, 'faculty_photos');
    }

    $faculty->update($validated);

    return redirect()->route('hr.faculty.show', $faculty->id)
      ->with('success', 'Faculty member updated successfully!');
  }

  /**
   * Remove the specified faculty member
   */
  public function destroy($id)
  {
    $faculty = Faculty::findOrFail($id);
    $faculty->delete();

    return redirect()->route('hr.faculty.index')
      ->with('success', 'Faculty member deleted successfully!');
  }

  /**
   * Mark faculty as left
   */
  public function markAsLeft(Request $request, $id)
  {
    $validated = $request->validate([
      'DOL' => 'required|date',
      'hr_remark' => 'nullable|string|max:1000',
    ]);

    $faculty = Faculty::findOrFail($id);
    $oldStatus = (int) $faculty->IS_LEFT;
    $faculty->update([
      'IS_LEFT' => 1,
      'DOL' => $validated['DOL'],
      'reactivation_date' => null,
      'hr_remark' => $validated['hr_remark'] ?? $faculty->hr_remark,
    ]);

    $this->logStatusEvent(
      $faculty,
      'deactivated',
      $validated['DOL'],
      $validated['hr_remark'] ?? null,
      $oldStatus,
      1
    );

    return redirect()->route('hr.faculty.show', $faculty->id)
      ->with('success', 'Faculty marked as left!');
  }

  /**
   * Restore faculty (mark as active)
   */
  public function restore(Request $request, $id)
  {
    $validated = $request->validate([
      'DOJ' => 'required|date',
      'hr_remark' => 'nullable|string|max:1000',
    ]);

    $faculty = Faculty::findOrFail($id);
    $oldStatus = (int) $faculty->IS_LEFT;
    $faculty->update([
      'IS_LEFT' => 0,
      'DOL' => null,
      'DOJ' => $validated['DOJ'],
      'reactivation_date' => null,
      'hr_remark' => $validated['hr_remark'] ?? $faculty->hr_remark,
    ]);

    $this->logStatusEvent(
      $faculty,
      'reactivated',
      $validated['DOJ'],
      $validated['hr_remark'] ?? null,
      $oldStatus,
      0
    );

    return redirect()->route('hr.faculty.show', $faculty->id)
      ->with('success', 'Faculty restored to active status!');
  }

  function deactivateFaculty(Request $request)
  {
    $validated = $request->validate([
      'id' => 'required|exists:faculties,id',
      'resignation_date' => 'required|date',
      'hr_remark' => 'nullable|string|max:1000',
    ]);

    $faculty = Faculty::findOrFail($validated['id']);
    $oldStatus = (int) $faculty->IS_LEFT;
    $faculty->update([
      'IS_LEFT' => 1,
      'DOL' => $validated['resignation_date'],
      'reactivation_date' => null,
      'hr_remark' => $validated['hr_remark'] ?? $faculty->hr_remark,
    ]);

    $this->logStatusEvent(
      $faculty,
      'deactivated',
      $validated['resignation_date'],
      $validated['hr_remark'] ?? null,
      $oldStatus,
      1
    );

    return redirect()->route('hr.faculty.index')
      ->with('success', 'Staff deactivated successfully!');
  }

  /**
   * Show dedicated per-faculty pay matrix page with prefilled values.
   */
  public function editIndividualPayMatrix($id)
  {
    $faculty = Faculty::with(['salaryMaster.payMatrix', 'hrDesignation', 'hrGradeLevel'])->findOrFail($id);

    $designations = HrDesignation::active()->ordered()->get();
    $gradeLevels = HrGradeLevel::active()->ordered()->get();

    if ($designations->isEmpty() || $gradeLevels->isEmpty()) {
      return redirect()
        ->route('hr.faculty.index')
        ->with('error', 'Please configure active Designation and Grade Level masters first.');
    }

    $salaryMaster = $faculty->salaryMaster;
    $payMatrix = $salaryMaster?->payMatrix;

    if (!$payMatrix) {
      $allowedEmploymentTypes = ['permanent', 'contractual', 'adhoc', 'guest', 'visiting'];
      $employmentType = in_array($faculty->employee_type, $allowedEmploymentTypes, true)
        ? $faculty->employee_type
        : 'contractual';

      $designationId = $faculty->hr_designation_id ?: $designations->first()->id;
      $gradeLevelId = $faculty->hr_grade_level_id ?: $gradeLevels->first()->id;

      $defaultDesignation = optional($designations->firstWhere('id', $designationId))->name;
      $defaultGradeLevel = optional($gradeLevels->firstWhere('id', $gradeLevelId))->name;

      $payMatrix = new HrPayMatrix([
        'matrix_name' => trim($faculty->full_name . ' Custom Matrix'),
        'designation_id' => $designationId,
        'grade_level_id' => $gradeLevelId,
        'designation' => $faculty->designation ?: $defaultDesignation,
        'grade_level' => $defaultGradeLevel,
        'employment_type' => $employmentType,
        'basic_salary' => (float) ($salaryMaster->basic_salary ?? 0),
        'da_percentage' => 0,
        'da_fixed' => (float) ($salaryMaster->da ?? 0),
        'hra_percentage' => 0,
        'hra_fixed' => (float) ($salaryMaster->hra ?? 0),
        'ta' => (float) ($salaryMaster->ta ?? 0),
        'medical_allowance' => (float) ($salaryMaster->medical_allowance ?? 0),
        'special_allowance' => (float) ($salaryMaster->special_allowance ?? 0),
        'research_allowance' => 0,
        'other_allowances' => (float) ($salaryMaster->other_allowances ?? 0),
        'annual_increment_percentage' => 0,
        'increment_month' => 7,
        'default_working_days' => (int) ($salaryMaster->working_days ?? 26),
        'status' => 'active',
        'effective_from' => $salaryMaster?->effective_from,
        'effective_to' => $salaryMaster?->effective_to,
        'remarks' => $salaryMaster?->remarks,
      ]);
    } else {
      // Render the form with faculty-specific override values while keeping base matrix mapping unchanged.
      $payMatrix = new HrPayMatrix([
        'id' => $payMatrix->id,
        'matrix_code' => $payMatrix->matrix_code,
        'matrix_name' => $payMatrix->matrix_name,
        'designation_id' => $payMatrix->designation_id,
        'grade_level_id' => $payMatrix->grade_level_id,
        'designation' => $payMatrix->designation,
        'grade_level' => $payMatrix->grade_level,
        'employment_type' => $payMatrix->employment_type,
        'pay_band' => $payMatrix->pay_band,
        'grade_pay' => $payMatrix->grade_pay,
        'basic_salary' => (float) ($salaryMaster->basic_salary ?? $payMatrix->basic_salary),
        // Salary master stores resolved DA/HRA amount; keep them editable as fixed values here.
        'da_percentage' => 0,
        'da_fixed' => (float) ($salaryMaster->da ?? $payMatrix->calculateDA()),
        'hra_percentage' => 0,
        'hra_fixed' => (float) ($salaryMaster->hra ?? $payMatrix->calculateHRA()),
        'ta' => (float) ($salaryMaster->ta ?? $payMatrix->ta),
        'medical_allowance' => (float) ($salaryMaster->medical_allowance ?? $payMatrix->medical_allowance),
        'special_allowance' => (float) ($salaryMaster->special_allowance ?? $payMatrix->special_allowance),
        'research_allowance' => 0,
        'other_allowances' => (float) ($salaryMaster->other_allowances ?? $payMatrix->other_allowances),
        'annual_increment_percentage' => $payMatrix->annual_increment_percentage,
        'increment_month' => $payMatrix->increment_month,
        'default_working_days' => (int) ($salaryMaster->working_days ?? $payMatrix->default_working_days),
        'status' => $payMatrix->status,
        'effective_from' => $salaryMaster?->effective_from ?? $payMatrix->effective_from,
        'effective_to' => $salaryMaster?->effective_to ?? $payMatrix->effective_to,
        'description' => $payMatrix->description,
        'remarks' => $salaryMaster?->remarks ?? $payMatrix->remarks,
      ]);
    }

    return view('hr.faculty.pay-matrix', compact('faculty', 'payMatrix', 'designations', 'gradeLevels'));
  }

  /**
   * Update or create a per-faculty customizable pay matrix and sync salary master.
   */
  public function updateIndividualPayMatrix(Request $request, $id)
  {
    $faculty = Faculty::with(['salaryMaster.payMatrix'])->findOrFail($id);
    $validated = $this->validatePayMatrixPayload($request);

    try {
      $validated = $this->normalizePayMatrixPayload($validated);

      DB::beginTransaction();

      $salaryMaster = $faculty->salaryMaster;
      if (!$salaryMaster || !$salaryMaster->pay_matrix_id) {
        DB::rollBack();
        return back()->with('error', 'No active pay matrix assignment found for this faculty.');
      }

      // Resolve DA/HRA using the same pay-matrix logic but do not update pay matrix itself.
      $resolvedDa = (float) (($validated['da_percentage'] ?? 0) > 0
        ? (($validated['basic_salary'] ?? 0) * ($validated['da_percentage'] ?? 0)) / 100
        : ($validated['da_fixed'] ?? 0));
      $resolvedHra = (float) (($validated['hra_percentage'] ?? 0) > 0
        ? (($validated['basic_salary'] ?? 0) * ($validated['hra_percentage'] ?? 0)) / 100
        : ($validated['hra_fixed'] ?? 0));

      $effectiveFrom = $validated['effective_from']
        ?? ($salaryMaster?->effective_from ? $salaryMaster->effective_from->format('Y-m-d') : date('Y-m-d'));

      $salaryPayload = [
        // Keep currently assigned pay matrix unchanged.
        'pay_matrix_id' => $salaryMaster->pay_matrix_id,
        'basic_salary' => $validated['basic_salary'],
        'da' => $resolvedDa,
        'hra' => $resolvedHra,
        'ta' => $validated['ta'] ?? 0,
        'medical_allowance' => $validated['medical_allowance'] ?? 0,
        'special_allowance' => $validated['special_allowance'] ?? 0,
        // Keep research allowance included at faculty salary level via merged allowance bucket.
        'other_allowances' => (float) ($validated['other_allowances'] ?? 0) + (float) ($validated['research_allowance'] ?? 0),
        // Deductions are entered by Accounts during payroll processing.
        'pf' => 0,
        'esi' => 0,
        'professional_tax' => 0,
        'tds' => 0,
        'other_deductions' => 0,
        'working_days' => $validated['default_working_days'] ?? $salaryMaster->working_days,
        'status' => 'active',
        'effective_from' => $effectiveFrom,
        'effective_to' => $validated['effective_to'] ?? null,
        'remarks' => 'Faculty-level override on Pay Matrix: ' . optional($salaryMaster->payMatrix)->matrix_code,
      ];

      if ($salaryMaster) {
        $salaryMaster->update($salaryPayload);
      } else {
        FacultySalaryMaster::create(array_merge($salaryPayload, [
          'faculty_id' => $faculty->id,
        ]));
      }

      DB::commit();

      return redirect()
        ->route('hr.faculty.pay-matrix.edit', $faculty->id)
        ->with('success', 'Faculty salary master earnings/allowances updated successfully without changing the assigned pay matrix.');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Faculty individual pay matrix update failed: ' . $e->getMessage());

      return back()
        ->withInput()
        ->with('error', 'Failed to update salary master. Please try again.');
    }
  }

  /**
   * Shared validation rules for faculty-customizable pay matrix form.
   */
  private function validatePayMatrixPayload(Request $request): array
  {
    return $request->validate([
      'matrix_name' => 'required|string|max:255',
      'designation_id' => 'required|exists:hr_designations,id',
      'grade_level_id' => 'required|exists:hr_grade_levels,id',
      'designation' => 'nullable|string|max:255',
      'grade_level' => 'nullable|string|max:255',
      'pay_band' => 'nullable|string|max:100',
      'grade_pay' => 'nullable|integer|min:0',
      'employment_type' => 'required|in:permanent,contractual,adhoc,guest,visiting',
      'basic_salary' => 'required|numeric|min:0',
      'da_percentage' => 'nullable|numeric|min:0|max:100',
      'da_fixed' => 'nullable|numeric|min:0',
      'hra_percentage' => 'nullable|numeric|min:0|max:100',
      'hra_fixed' => 'nullable|numeric|min:0',
      'ta' => 'nullable|numeric|min:0',
      'medical_allowance' => 'nullable|numeric|min:0',
      'special_allowance' => 'nullable|numeric|min:0',
      'research_allowance' => 'nullable|numeric|min:0',
      'other_allowances' => 'nullable|numeric|min:0',
      'annual_increment_percentage' => 'nullable|numeric|min:0|max:100',
      'increment_month' => 'nullable|integer|min:1|max:12',
      'default_working_days' => 'required|integer|min:1|max:31',
      'status' => 'required|in:active,inactive,archived',
      'effective_from' => 'nullable|date',
      'effective_to' => 'nullable|date|after:effective_from',
      'description' => 'nullable|string',
      'remarks' => 'nullable|string',
    ]);
  }

  /**
   * Normalize optional values to match pay matrix master defaults.
   */
  private function normalizePayMatrixPayload(array $validated): array
  {
    $validated['increment_month'] = $validated['increment_month'] ?? 7;
    $validated['annual_increment_percentage'] = $validated['annual_increment_percentage'] ?? 0;
    $validated['da_percentage'] = $validated['da_percentage'] ?? 0;
    $validated['da_fixed'] = $validated['da_fixed'] ?? 0;
    $validated['hra_percentage'] = $validated['hra_percentage'] ?? 0;
    $validated['hra_fixed'] = $validated['hra_fixed'] ?? 0;
    $validated['ta'] = $validated['ta'] ?? 0;
    $validated['medical_allowance'] = $validated['medical_allowance'] ?? 0;
    $validated['special_allowance'] = $validated['special_allowance'] ?? 0;
    $validated['research_allowance'] = $validated['research_allowance'] ?? 0;
    $validated['other_allowances'] = $validated['other_allowances'] ?? 0;

    // Deductions are managed by Accounts during payroll processing.
    $validated['pf_percentage'] = 0;
    $validated['pf_fixed'] = 0;
    $validated['esi_percentage'] = 0;
    $validated['esi_fixed'] = 0;
    $validated['professional_tax'] = 0;
    $validated['tds_percentage'] = 0;
    $validated['other_deductions'] = 0;

    return $validated;
  }

  /**
   * Create a faculty status transition audit record.
   */
  private function logStatusEvent(Faculty $faculty, string $eventType, ?string $statusOn, ?string $remark, ?int $oldStatus, ?int $newStatus): void
  {
    HrFacultyStatusHistory::create([
      'faculty_id' => $faculty->id,
      'event_type' => $eventType,
      'status_on' => $statusOn,
      'remark' => $remark,
      'old_status' => $oldStatus,
      'new_status' => $newStatus,
      'acted_by' => Auth::id(),
    ]);
  }
}
