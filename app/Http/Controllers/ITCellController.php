<?php

namespace App\Http\Controllers;

use App\Exports\StudentDataExport;
use App\Exports\StudentLibraryCodeExport;
use App\Models\AcademicPathwayMaster;
use App\Models\AdmissionApplication;
use App\Models\AnnualPromotionLog;
use App\Models\BatchMaster;
use App\Models\BloodGroupMaster;
use App\Models\Campus;
use App\Models\DegreeTrackMaster;
use App\Models\DepartmentMaster;
use App\Models\IntegratedProgramSublayerSetting;
use App\Models\LateralEntryAuditLog;
use App\Models\NationalityMaster;
use App\Models\ProgramMaster;
use App\Models\ProgramCourseMaster;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\ReligionMaster;
use App\Models\Semester;
use App\Models\StudentCourseInfo;
use App\Models\StudentCampusTransferLog;
use App\Models\StudentSemesterConfig;
use App\Models\StudentMaster;
use App\Models\StudentProgram;
use App\Models\Subject;
use App\Models\SubjectHasStudentProgam;
use App\Models\UserHasRole;
use App\Services\StudentRosterEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ITCellController extends Controller
{

    public function integratedProgramSublayersIndex()
    {
        if (!Schema::hasTable('integrated_program_sublayer_settings')) {
            return back()->with('error', 'Integrated sublayer settings table not found. Please run migrations first.');
        }

        $programs = StudentProgram::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $settings = IntegratedProgramSublayerSetting::query()
            ->with('studentProgram:id,code,name')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.itcell.integrated-program-sublayers', [
            'programs' => $programs,
            'settings' => $settings,
        ]);
    }

    public function integratedProgramSublayersStore(Request $request)
    {
        if (!Schema::hasTable('integrated_program_sublayer_settings')) {
            return back()->with('error', 'Integrated sublayer settings table not found. Please run migrations first.');
        }

        $request->validate([
            'student_program_id' => [
                'required',
                'integer',
                'exists:student_program,id',
                Rule::unique('integrated_program_sublayer_settings', 'student_program_id')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'ug_max_year' => 'required|integer|min:1|max:10',
            'ug_label' => 'nullable|string|max:50',
            'pg_label' => 'nullable|string|max:50',
            'is_active' => 'nullable|in:0,1',
        ]);

        IntegratedProgramSublayerSetting::create([
            'student_program_id' => (int) $request->student_program_id,
            'ug_max_year' => (int) $request->ug_max_year,
            'ug_label' => trim((string) $request->input('ug_label', '')) ?: 'UG Layer',
            'pg_label' => trim((string) $request->input('pg_label', '')) ?: 'PG Layer',
            'is_active' => (int) $request->input('is_active', 1),
        ]);

        return back()->with('success', 'Integrated program sublayer setting created successfully.');
    }

    public function integratedProgramSublayersUpdate(Request $request, int $id)
    {
        if (!Schema::hasTable('integrated_program_sublayer_settings')) {
            return back()->with('error', 'Integrated sublayer settings table not found. Please run migrations first.');
        }

        $setting = IntegratedProgramSublayerSetting::query()->find($id);
        if (!$setting) {
            return back()->with('error', 'Setting not found.');
        }

        $request->validate([
            'ug_max_year' => 'required|integer|min:1|max:10',
            'ug_label' => 'nullable|string|max:50',
            'pg_label' => 'nullable|string|max:50',
            'is_active' => 'required|in:0,1',
        ]);

        $setting->update([
            'ug_max_year' => (int) $request->ug_max_year,
            'ug_label' => trim((string) $request->input('ug_label', '')) ?: 'UG Layer',
            'pg_label' => trim((string) $request->input('pg_label', '')) ?: 'PG Layer',
            'is_active' => (int) $request->is_active,
        ]);

        return back()->with('success', 'Integrated program sublayer setting updated successfully.');
    }

    public function integratedProgramSublayersToggle(int $id)
    {
        if (!Schema::hasTable('integrated_program_sublayer_settings')) {
            return back()->with('error', 'Integrated sublayer settings table not found. Please run migrations first.');
        }

        $setting = IntegratedProgramSublayerSetting::query()->find($id);
        if (!$setting) {
            return back()->with('error', 'Setting not found.');
        }

        $setting->update([
            'is_active' => (int) (!$setting->is_active),
        ]);

        return back()->with('success', 'Setting status changed successfully.');
    }

    public function integratedStudentShiftIndex(Request $request)
    {
        if (!Schema::hasTable('integrated_program_sublayer_settings')) {
            return back()->with('error', 'Integrated sublayer settings table not found. Please run migrations first.');
        }

        $selectedBatchId = (int) $request->input('batch_id', 0);
        $selectedIntegratedProgramId = (int) $request->input('integrated_program_id', 0);
        $search = trim((string) $request->input('search', ''));

        $batches = BatchMaster::query()->orderByDesc('id')->get(['id', 'batch_name']);

        $integratedProgramIds = IntegratedProgramSublayerSetting::query()
            ->where('is_active', 1)
            ->pluck('student_program_id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $integratedPrograms = StudentProgram::query()
            ->whereIn('id', $integratedProgramIds->all())
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        if ($selectedIntegratedProgramId > 0 && !$integratedProgramIds->contains($selectedIntegratedProgramId)) {
            $selectedIntegratedProgramId = 0;
        }

        $targetCombinations = collect();
        if ($selectedBatchId > 0) {
            $integratedSubjectIds = collect();
            if ($selectedIntegratedProgramId > 0) {
                $integratedSubjectIds = SubjectHasStudentProgam::query()
                    ->where('batch_id', $selectedBatchId)
                    ->where('student_program_id', $selectedIntegratedProgramId)
                    ->pluck('subject_id')
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values();
            }

            $targetCombinations = SubjectHasStudentProgam::query()
                ->with([
                    'studentprograminfo:id,code,name,program_type',
                    'studentprograminfo.programtypemaster:id,name',
                    'subjectmaster:id,code,title',
                ])
                ->where('batch_id', $selectedBatchId)
                ->when($integratedProgramIds->isNotEmpty(), fn($query) => $query->whereNotIn('student_program_id', $integratedProgramIds->all()))
                ->when($selectedIntegratedProgramId > 0, fn($query) => $query->where('student_program_id', '!=', $selectedIntegratedProgramId))
                ->when(
                    $selectedIntegratedProgramId > 0,
                    fn($query) => $integratedSubjectIds->isNotEmpty()
                        ? $query->whereIn('subject_id', $integratedSubjectIds->all())
                        : $query->whereRaw('1=0')
                )
                ->where(function ($query) {
                    $query->whereHas('studentprograminfo.programtypemaster', function ($typeQuery) {
                        $typeQuery->whereRaw("UPPER(name) LIKE '%UG%'")
                            ->whereRaw("UPPER(name) NOT LIKE '%PG%'")
                            ->whereRaw("UPPER(name) NOT LIKE '%INTEGRATED%'");
                    })->orWhereHas('studentprograminfo', function ($programQuery) {
                        $programQuery->where(function ($rawProgramTypeQuery) {
                            $rawProgramTypeQuery->where('program_type', 1)
                                ->orWhere('program_type', '1')
                                ->orWhereRaw("UPPER(CAST(program_type AS CHAR)) = 'UG'");
                        });
                    });
                })
                ->orderBy('student_program_id')
                ->orderBy('subject_id')
                ->get(['id', 'student_program_id', 'subject_id', 'batch_id', 'program_type'])
                ->filter(function ($row) {
                    $raw = strtoupper(trim((string) ($row->program_type ?? '')));
                    $masterRaw = strtoupper(trim((string) (optional(optional($row)->studentprograminfo)->programtypemaster->name ?? '')));

                    $isPgLike = function (string $value): bool {
                        return str_contains($value, 'PG') || str_contains($value, 'POST') || str_contains($value, 'INTEGRATED') || str_contains($value, '+');
                    };

                    $isUgLike = function (string $value): bool {
                        return str_contains($value, 'UG') || str_contains($value, 'UNDER');
                    };

                    if ($raw !== '') {
                        if ($isUgLike($raw) && !$isPgLike($raw)) {
                            return true;
                        }

                        if (ctype_digit($raw)) {
                            return (int) $raw === 1;
                        }
                    }

                    if ($masterRaw !== '') {
                        return $isUgLike($masterRaw) && !$isPgLike($masterRaw);
                    }

                    return false;
                })
                ->unique(function ($row) {
                    return (int) ($row->student_program_id ?? 0) . '|' . (int) ($row->subject_id ?? 0) . '|' . (int) ($row->batch_id ?? 0);
                })
                ->values();
        }

        $students = collect();
        if ($selectedBatchId > 0 && $integratedProgramIds->isNotEmpty()) {
            $hasIntegratedOriginProgramId = Schema::hasColumn('student_masters', 'integrated_origin_program_id');
            $studentsQuery = StudentMaster::query()
                ->with([
                    'batchmaster:id,batch_name',
                    'stdprogramenrolled:id,code,name',
                    'subjectmaster:id,code,title',
                ])
                ->where('batch', $selectedBatchId)
                ->where(function ($query) use ($selectedIntegratedProgramId, $integratedProgramIds, $hasIntegratedOriginProgramId) {
                    if ($selectedIntegratedProgramId > 0) {
                        $query->where('new_program_id', $selectedIntegratedProgramId);
                        if ($hasIntegratedOriginProgramId) {
                            $query->orWhere('integrated_origin_program_id', $selectedIntegratedProgramId);
                        }
                        return;
                    }

                    $query->whereIn('new_program_id', $integratedProgramIds->all());
                    if ($hasIntegratedOriginProgramId) {
                        $query->orWhereIn('integrated_origin_program_id', $integratedProgramIds->all());
                    }
                });

            if (Schema::hasColumn('student_masters', 'is_deleted')) {
                $studentsQuery->where('is_deleted', 0);
            }

            if (Schema::hasColumn('student_masters', 'is_left')) {
                $studentsQuery->where('is_left', 0);
            }

            if ($search !== '') {
                $studentsQuery->where(function ($query) use ($search) {
                    $query->where('roll_no', 'like', '%' . $search . '%')
                        ->orWhere('register_no', 'like', '%' . $search . '%')
                        ->orWhere('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            }

            $studentSelectColumns = ['id', 'roll_no', 'register_no', 'first_name', 'last_name', 'batch', 'new_program_id', 'selected_combo_id', 'current_year'];
            if (Schema::hasColumn('student_masters', 'is_integrated_program_origin')) {
                $studentSelectColumns[] = 'is_integrated_program_origin';
            }
            if ($hasIntegratedOriginProgramId) {
                $studentSelectColumns[] = 'integrated_origin_program_id';
            }

            $students = $studentsQuery
                ->orderBy('roll_no')
                ->orderBy('first_name')
                ->get($studentSelectColumns);

            $programLabelMap = StudentProgram::query()
                ->whereIn('id', $students->pluck('new_program_id')
                    ->merge($students->pluck('integrated_origin_program_id'))
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values()
                    ->all())
                ->get(['id', 'code', 'name'])
                ->mapWithKeys(function ($program) {
                    $label = trim(((string) ($program->code ?? '')) . (((string) ($program->code ?? '')) !== '' ? ' - ' : '') . ((string) ($program->name ?? '')));
                    return [(int) $program->id => $label !== '' ? $label : ('Program #' . (int) $program->id)];
                });

            $students = $students->map(function ($student) use ($integratedProgramIds, $selectedIntegratedProgramId, $programLabelMap) {
                $currentProgramId = (int) ($student->new_program_id ?? 0);
                $originProgramId = (int) ($student->integrated_origin_program_id ?? 0);
                $isCurrentIntegrated = $integratedProgramIds->contains($currentProgramId);

                $sourceIntegratedProgramId = $originProgramId > 0 ? $originProgramId : ($isCurrentIntegrated ? $currentProgramId : 0);
                $isShiftedFromIntegrated = $sourceIntegratedProgramId > 0 && $currentProgramId > 0 && $currentProgramId !== $sourceIntegratedProgramId;

                $student->actual_program_label = $programLabelMap[$currentProgramId] ?? (trim((string) (optional($student->stdprogramenrolled)->code ?? '')) !== ''
                    ? trim((string) (optional($student->stdprogramenrolled)->code ?? '') . ' - ' . (string) (optional($student->stdprogramenrolled)->name ?? ''))
                    : (string) (optional($student->stdprogramenrolled)->name ?? '-'));

                $student->source_integrated_program_label = $sourceIntegratedProgramId > 0
                    ? ($programLabelMap[$sourceIntegratedProgramId] ?? ('Program #' . $sourceIntegratedProgramId))
                    : '-';

                $student->is_shifted_from_integrated = $isShiftedFromIntegrated ? 1 : 0;
                $student->can_shift_now = ($selectedIntegratedProgramId > 0 && $currentProgramId === $selectedIntegratedProgramId) ? 1 : 0;

                return $student;
            })->values();
        }

        return view('admin.itcell.integrated-student-shift', [
            'batches' => $batches,
            'integratedPrograms' => $integratedPrograms,
            'targetCombinations' => $targetCombinations,
            'students' => $students,
            'selectedBatchId' => $selectedBatchId,
            'selectedIntegratedProgramId' => $selectedIntegratedProgramId,
            'search' => $search,
        ]);
    }

    public function integratedStudentShiftStore(Request $request)
    {
        if (!Schema::hasTable('integrated_program_sublayer_settings')) {
            return back()->with('error', 'Integrated sublayer settings table not found. Please run migrations first.');
        }

        $request->validate([
            'batch_id' => 'required|integer|exists:batch_masters,id',
            'integrated_program_id' => 'required|integer|min:1|exists:student_program,id',
            'target_combination_id' => 'required|integer|exists:subject_has_student_progams,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|integer|exists:student_masters,id',
            'remarks' => 'nullable|string|max:500',
            'search' => 'nullable|string|max:100',
        ]);

        $batchId = (int) $request->batch_id;
        $integratedProgramId = (int) $request->integrated_program_id;
        $targetCombinationId = (int) $request->target_combination_id;
        $studentIds = collect((array) $request->input('student_ids', []))
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $isConfiguredIntegratedProgram = IntegratedProgramSublayerSetting::query()
            ->where('student_program_id', $integratedProgramId)
            ->where('is_active', 1)
            ->exists();

        if (!$isConfiguredIntegratedProgram) {
            return back()->with('error', 'Selected source program is not configured as active integrated program.');
        }

        $targetCombination = SubjectHasStudentProgam::query()
            ->where('id', $targetCombinationId)
            ->where('batch_id', $batchId)
            ->first(['id', 'student_program_id', 'subject_id', 'batch_id']);

        if (!$targetCombination) {
            return back()->with('error', 'Target combination is invalid for selected batch.');
        }

        if ((int) $targetCombination->student_program_id === $integratedProgramId) {
            return back()->with('error', 'Target combination must be a non-integrated destination program.');
        }

        $hasIntegratedOriginFlag = Schema::hasColumn('student_masters', 'is_integrated_program_origin');
        $hasIntegratedOriginProgramId = Schema::hasColumn('student_masters', 'integrated_origin_program_id');
        $hasIntegratedShiftedAt = Schema::hasColumn('student_masters', 'integrated_shifted_at');
        $hasSelectedComboColumn = Schema::hasColumn('student_masters', 'selected_combo_id');
        $hasAcademicDeptColumn = Schema::hasColumn('student_masters', 'academic_dept_id');
        $hasDepartmentColumn = Schema::hasColumn('student_masters', 'department');
        $hasShiftLogTable = Schema::hasTable('integrated_program_student_shifts');

        $eligibleStudents = StudentMaster::query()
            ->whereIn('id', $studentIds->all())
            ->where('batch', $batchId)
            ->where('new_program_id', $integratedProgramId)
            ->when(Schema::hasColumn('student_masters', 'is_deleted'), fn($query) => $query->where('is_deleted', 0))
            ->when(Schema::hasColumn('student_masters', 'is_left'), fn($query) => $query->where('is_left', 0))
            ->get(['id', 'new_program_id', 'selected_combo_id']);

        if ($eligibleStudents->isEmpty()) {
            return back()->with('error', 'No eligible integrated students found to shift.');
        }

        DB::transaction(function () use (
            $eligibleStudents,
            $targetCombination,
            $batchId,
            $integratedProgramId,
            $request,
            $hasIntegratedOriginFlag,
            $hasIntegratedOriginProgramId,
            $hasIntegratedShiftedAt,
            $hasSelectedComboColumn,
            $hasAcademicDeptColumn,
            $hasDepartmentColumn,
            $hasShiftLogTable
        ) {
            foreach ($eligibleStudents as $student) {
                $updatePayload = [
                    'new_program_id' => (int) $targetCombination->student_program_id,
                ];

                if ($hasSelectedComboColumn) {
                    $updatePayload['selected_combo_id'] = (int) $targetCombination->subject_id;
                }

                if ($hasAcademicDeptColumn) {
                    $updatePayload['academic_dept_id'] = (int) $targetCombination->subject_id;
                }

                if ($hasDepartmentColumn) {
                    $updatePayload['department'] = (int) $targetCombination->subject_id;
                }

                if ($hasIntegratedOriginFlag) {
                    $updatePayload['is_integrated_program_origin'] = 1;
                }

                if ($hasIntegratedOriginProgramId) {
                    $updatePayload['integrated_origin_program_id'] = (int) $integratedProgramId;
                }

                if ($hasIntegratedShiftedAt) {
                    $updatePayload['integrated_shifted_at'] = now();
                }

                // Intentional: roll_no is never changed in integrated shift.
                StudentMaster::query()->where('id', (int) $student->id)->update($updatePayload);

                if ($hasShiftLogTable) {
                    DB::table('integrated_program_student_shifts')->insert([
                        'student_id' => (int) $student->id,
                        'batch_id' => $batchId,
                        'from_program_id' => $integratedProgramId,
                        'to_program_id' => (int) $targetCombination->student_program_id,
                        'from_combination_id' => null,
                        'to_combination_id' => (int) $targetCombination->id,
                        'origin_program_id' => $hasIntegratedOriginProgramId ? (int) $integratedProgramId : null,
                        'remarks' => trim((string) $request->input('remarks', '')),
                        'shifted_by' => (int) (Auth::id() ?? 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        $query = [
            'batch_id' => $batchId,
            'integrated_program_id' => $integratedProgramId,
        ];
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query['search'] = $search;
        }

        return redirect()
            ->route('itcell.integrated-student-shift.index', $query)
            ->with('success', $eligibleStudents->count() . ' integrated student(s) shifted. Roll number remains unchanged.');
    }

    private function deriveCurrentYearFromSemester(int $semester): int
    {
        return max(1, (int) ceil($semester / 2));
    }

    function verifyPayment(int $id)
    {
        $applicationRecord = AdmissionApplication::find($id);
        if (!$applicationRecord) {
            return back()->with('error', 'Application not found.');
        }
        $txnid = $applicationRecord->application_code;
        // Call the payment gateway API to verify payment status
        // This is a placeholder. You need to implement actual API call and response handling based on your payment gateway's documentation.

        $response = StaticController::easebuzz_verifyPaymentWithHash($txnid);
        if ($response['status'] == false) {
            return back()->with('error', $response['msg']);
        }
        $data =  $response['msg']['0'];
        return view('admin.itcell.ez-payment-verification', ['data' => $data]);
    }

    function updateApplicationPayment(Request $request)
    {
        $request->validate([
            'application_code' => 'required|string',
        ]);
        $id = $request->id;
        $applicationRecord = AdmissionApplication::find($id);
        if (!$applicationRecord) {
            return back()->with('error', 'Application not found.');
        }

        // Update the payment status in the database
        $txnid = $request->application_code;
        $applicationRecord->application_code = $txnid;
        $applicationRecord->save();

        //verify payment again to update the status
        $txnid = $request->application_code;
        $response = StaticController::easebuzz_verifyPaymentWithHash($txnid);
        if ($response['status'] == false) {
            return back()->with('error', $response['msg']);
        }
        $data =  $response['msg']['0'];
        if ($data['status'] == 'success') {
            $applicationRecord->update([
                'payment_gateway_ref' => $data['easepayid'],
                'captured_amount' => $data['amount'],
                'hash' => $data['hash'],
                'payment_gateway_status' => $data['status'],
                'msg' => $data['error_Message'],
            ]);
        }

        return back()->with('success', 'Payment status updated successfully.');
    }

    function promotionPrepareList(Request $request)
    {
        $request->validate([
            'batch' => 'required',
            'campus' => 'required'
        ]);
        $batch = $request->batch;
        $campus = $request->campus;
        $data = StudentMaster::with('batchmaster')->where('batch', $batch)->where('campus_id', $campus)->get();

        return view('admin.itcell.promotion-list', ['students' => $data, 'batch' => $batch, 'campus' => $campus]);
    }

    //annual promotion
    function annualStudentPromotion(Request $request)
    {
        $request->validate([
            'batch' => 'required',
            'campus' => 'required',
            'student' => 'required|array|min:1'
        ]);
        $campus = $request->campus;
        //create Annual Promotion logs
        $student = $request->student;
        DB::beginTransaction();
        try {
            $userId = Auth::user()->id;
            foreach ($student as $studentId => $status) {
                $studentInfo = StudentMaster::find($studentId);
                $currentyear = $studentInfo->current_year;

                //action checker
                if ($status == 'absent') {
                    $promoted_to = $currentyear;
                }

                if ($status == 'present') {
                    $promoted_to = $currentyear + 1;
                }

                //Recording Logs
                AnnualPromotionLog::create(
                    [
                        'batch' => $request->batch,
                        'campus' => $campus,
                        'student_id' => $studentId,
                        'promoted_from_year' =>  $currentyear,
                        'promoted_to_year' => $promoted_to,
                        'status' => $status == 'present' ? 'promoted' : 'not promoted',
                        'created_by' => $userId,
                        'updated_by' => $userId
                    ],

                );

                //Making Promotional Changes in StudentMaster Table

                $studentInfo->update([
                    'current_year' => $promoted_to
                ]);
            }

            DB::commit();


            //return to sonada  to student master
            if ($campus == 1) {
                return redirect()
                    ->route('sonada.studentmaster')
                    ->with('success', 'Promotion Done Successfully!');
            }
            //return to siliguri student master
            if ($campus == 2) {
                return redirect()
                    ->route('siliguri.studentmaster')
                    ->with('success', 'Promotion Done Successfully!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to promote students. Please try again.');
        }
    }

    function annualStudentPromotionLogs(int $campusid)
    {
        $data =  AnnualPromotionLog::with([
            'studentmaster',
            'batchmaster',
            'campusmaster'
        ])->where('campus', $campusid)->latest()->get();

        return view('admin.itcell.promotion-logs', ['data' => $data]);
    }

    function semesterPromotionPrepareList(Request $request)
    {
        $request->validate([
            'batch' => 'required',
            'campus' => 'required'
        ]);

        $batch = $request->batch;
        $campus = $request->campus;

        $students = StudentMaster::with([
            'batchmaster',
            'activeSemesterConfig'
        ])->where('batch', $batch)
            ->where('campus_id', $campus)
            ->get();

        return view('admin.itcell.semester-promotion-list', [
            'students' => $students,
            'batch' => $batch,
            'campus' => $campus
        ]);
    }

    function bulkSemesterPromotion(Request $request)
    {
        $request->validate([
            'batch' => 'required',
            'campus' => 'required',
            'student' => 'required|array|min:1'
        ]);

        $campus = $request->campus;
        $student = $request->student;

        DB::beginTransaction();
        try {
            foreach ($student as $studentId => $status) {
                $studentInfo = StudentMaster::with('activeSemesterConfig')->find($studentId);
                if (!$studentInfo) {
                    continue;
                }

                $currentSemester = (int) ($studentInfo->activeSemesterConfig->semester_id ?? 1);
                $promotedTo = $status === 'present' ? $currentSemester + 1 : $currentSemester;

                StudentSemesterConfig::where('student_id', $studentId)->update([
                    'current_semester' => 0,
                ]);

                StudentSemesterConfig::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'semester_id' => (string) $promotedTo,
                    ],
                    [
                        'current_semester' => 1,
                    ]
                );

                $studentInfo->update([
                    'current_year' => $this->deriveCurrentYearFromSemester($promotedTo),
                ]);
            }

            DB::commit();

            if ($campus == 1) {
                return redirect()
                    ->route('sonada.studentmaster')
                    ->with('success', 'Semester promotion completed successfully.');
            }

            if ($campus == 2) {
                return redirect()
                    ->route('siliguri.studentmaster')
                    ->with('success', 'Semester promotion completed successfully.');
            }

            return redirect()->back()->with('success', 'Semester promotion completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to promote semester in bulk. Please try again.');
        }
    }

    function demoteStudentSemester(int $studentId, Request $request)
    {
        DB::beginTransaction();
        try {
            $studentInfo = StudentMaster::with('activeSemesterConfig')->findOrFail($studentId);
            $currentSemester = (int) ($studentInfo->activeSemesterConfig->semester_id ?? 1);

            if ($currentSemester <= 1) {
                $message = 'Semester cannot be demoted below 1.';
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['message' => $message], 422);
                }
                return back()->with('error', $message);
            }

            $demotedSemester = $currentSemester - 1;

            $targetSemesterConfig = StudentSemesterConfig::where('student_id', $studentId)
                ->where('semester_id', (string) $demotedSemester)
                ->first();

            if (!$targetSemesterConfig) {
                $message = 'Demotion target semester record not found. No new record was created.';
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['message' => $message], 422);
                }
                return back()->with('error', $message);
            }

            StudentSemesterConfig::where('student_id', $studentId)->update([
                'current_semester' => 0,
            ]);

            $targetSemesterConfig->update([
                'current_semester' => 1,
            ]);

            $studentInfo->update([
                'current_year' => $this->deriveCurrentYearFromSemester($demotedSemester),
            ]);

            DB::commit();

            $message = 'Semester demoted successfully.';
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'semester' => $demotedSemester,
                    'current_year' => $studentInfo->current_year,
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Failed to demote semester.'], 500);
            }
            return back()->with('error', 'Failed to demote semester.');
        }
    }

    function studentPathwayMapper(Request $request)
    {
        $isPrincipalMonitoring = $request->routeIs('principal.pathway.mapper');
        if ($isPrincipalMonitoring) {
            $role = (string) UserHasRole::where('user_id', Auth::id())->value('role_name');
            if ($role !== 'principal') {
                abort(403, 'Unauthorized access.');
            }
        }

        $batches = BatchMaster::orderByDesc('id')->get();
        $pathways = AcademicPathwayMaster::orderBy('name')->get();
        $degreeTracks = DegreeTrackMaster::orderBy('name')->get();
        $selectedProgramIds = collect((array) $request->input('program_ids', []))
            ->filter(fn($id) => is_numeric($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();

        $enrolledProgramIds = StudentMaster::whereNotNull('new_program_id')
            ->distinct()
            ->pluck('new_program_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        $enrolledPrograms = StudentProgram::query()
            ->whereIn('id', $enrolledProgramIds)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $batchProgramMap = StudentMaster::query()
            ->select('batch', 'new_program_id')
            ->whereNotNull('batch')
            ->whereNotNull('new_program_id')
            ->distinct()
            ->get()
            ->groupBy('batch')
            ->map(fn($rows) => $rows->pluck('new_program_id')->map(fn($id) => (int) $id)->values()->all())
            ->toArray();

        $subjects = Subject::query()
            ->orderBy('title')
            ->get(['id', 'title', 'campus_id']);

        $students = collect();

        if ($request->filled('batch_id') || !empty($selectedProgramIds)) {
            $query = StudentMaster::with([
                'batchmaster:id,batch_name',
                'stdprogramenrolled:id,name,code',
                'academicpathway:id,name',
                'degreetrack:id,name',
                'singleselection:id,title',
                'activeSemesterConfig:id,student_id,semester_id,current_semester',
            ]);

            if ($request->filled('batch_id')) {
                $query->where('batch', $request->batch_id);
            }

            if (!empty($selectedProgramIds)) {
                $query->whereIn('new_program_id', $selectedProgramIds);
            }

            if ($request->filled('pathway_type') && in_array((int) $request->pathway_type, [1, 2], true)) {
                $query->where('academic_pathway_id', (int) $request->pathway_type);
            }

            if ($request->filled('current_semester')) {
                $query->whereHas('activeSemesterConfig', function ($semQuery) use ($request) {
                    $semQuery->where('semester_id', (string) $request->current_semester);
                });
            }

            $students = $query->orderBy('first_name')->orderBy('last_name')->get();
        }

        return view('admin.itcell.student-pathway-mapper', [
            'batches' => $batches,
            'pathways' => $pathways,
            'degreeTracks' => $degreeTracks,
            'enrolledPrograms' => $enrolledPrograms,
            'batchProgramMap' => $batchProgramMap,
            'subjects' => $subjects,
            'students' => $students,
            'filters' => [
                'batch_id' => $request->input('batch_id'),
                'program_ids' => $selectedProgramIds,
                'pathway_type' => $request->input('pathway_type'),
                'current_semester' => $request->input('current_semester'),
            ],
            'isPrincipalMonitoring' => $isPrincipalMonitoring,
        ]);
    }

    function studentPathwayMapperBulkUpdate(Request $request)
    {
        $selectedProgramIds = collect((array) $request->input('program_ids', []))
            ->filter(fn($id) => is_numeric($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();

        $request->validate([
            'batch_id' => 'nullable|integer|exists:batch_masters,id|required_without:program_ids',
            'program_ids' => 'nullable|array|required_without:batch_id',
            'program_ids.*' => 'integer|exists:student_program,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:student_masters,id',
            'academic_pathway_id' => 'nullable|integer|exists:academic_pathway_masters,id',
            'degree_track_id' => 'nullable|integer|exists:degree_track_masters,id',
            'selected_combo_id' => 'nullable|integer|exists:subjects,id',
        ]);

        if (
            !$request->filled('academic_pathway_id')
            && !$request->filled('degree_track_id')
            && !$request->filled('selected_combo_id')
        ) {
            return back()->with('error', 'Please select at least one mapping field to update.');
        }

        $updateData = [];
        if ($request->filled('academic_pathway_id')) {
            $updateData['academic_pathway_id'] = (int) $request->academic_pathway_id;
        }
        if ($request->filled('degree_track_id')) {
            $updateData['degree_track_id'] = (int) $request->degree_track_id;
        }
        if ($request->filled('selected_combo_id')) {
            $updateData['selected_combo_id'] = (int) $request->selected_combo_id;
        }

        $updateQuery = StudentMaster::whereIn('id', $request->student_ids);

        if ($request->filled('batch_id')) {
            $updateQuery->where('batch', (int) $request->batch_id);
        }

        if (!empty($selectedProgramIds)) {
            $updateQuery->whereIn('new_program_id', $selectedProgramIds);
        }

        $updatedCount = $updateQuery->update($updateData);

        $query = [
            'batch_id' => $request->batch_id,
            'program_ids' => $selectedProgramIds,
            'pathway_type' => $request->input('pathway_type'),
            'current_semester' => $request->input('current_semester'),
        ];

        return redirect()
            ->route('itcell.pathway.mapper', array_filter($query, fn($value) => $value !== null && $value !== ''))
            ->with('success', $updatedCount . ' student(s) updated successfully.');
    }

    public function studentMdcSelectionIndex(Request $request)
    {
        $batches = BatchMaster::query()->orderByDesc('batch_name')->get(['id', 'batch_name']);
        $semesters = Semester::query()->orderBy('id')->get(['id', 'title']);
        $campuses = Campus::query()->orderBy('name')->get(['id', 'name']);

        $selectedBatchId = (int) $request->input('batch_id', 0);
        if ($selectedBatchId < 0) {
            $selectedBatchId = 0;
        }

        $selectedSemesterId = (int) $request->input('semester_id', 0);
        if ($selectedSemesterId < 0) {
            $selectedSemesterId = 0;
        }

        $selectedProgramId = (int) $request->input('program_id', 0);
        if ($selectedProgramId < 0) {
            $selectedProgramId = 0;
        }

        $selectedCampusId = (int) $request->input('campus_id', 0);
        if ($selectedCampusId < 0) {
            $selectedCampusId = 0;
        }

        $search = trim((string) $request->input('search', ''));

        $enrolledProgramsQuery = StudentProgram::query()
            ->select('student_program.id', 'student_program.code', 'student_program.name')
            ->join('student_masters', 'student_masters.new_program_id', '=', 'student_program.id')
            ->whereNotNull('student_masters.new_program_id')
            ->where('student_masters.is_deleted', 0)
            ->where('student_masters.is_left', 0);

        if ($selectedBatchId > 0) {
            $enrolledProgramsQuery->where('student_masters.batch', $selectedBatchId);
        }

        if ($selectedCampusId > 0) {
            $enrolledProgramsQuery->where('student_masters.campus_id', $selectedCampusId);
        }

        $enrolledPrograms = $enrolledProgramsQuery
            ->distinct()
            ->orderBy('student_program.name')
            ->get();

        if ($selectedProgramId > 0 && !$enrolledPrograms->contains('id', $selectedProgramId)) {
            $selectedProgramId = 0;
        }

        $studentsQuery = StudentMaster::query()
            ->with([
                'batchmaster:id,batch_name',
                'campusmaster:id,name',
                'stdprogramenrolled:id,name,code',
                'activeSemesterConfig:id,student_id,semester_id,current_semester',
            ])
            ->where('is_deleted', 0)
            ->where('is_left', 0);

        if ($selectedBatchId > 0) {
            $studentsQuery->where('batch', $selectedBatchId);
        }

        if ($selectedSemesterId > 0) {
            $studentsQuery->whereHas('activeSemesterConfig', function ($query) use ($selectedSemesterId) {
                $query->where('semester_id', (string) $selectedSemesterId);
            });
        }

        if ($selectedProgramId > 0) {
            $studentsQuery->where('new_program_id', $selectedProgramId);
        }

        if ($selectedCampusId > 0) {
            $studentsQuery->where('campus_id', $selectedCampusId);
        }

        if ($search !== '') {
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('roll_no', 'like', '%' . $search . '%')
                    ->orWhere('register_no', 'like', '%' . $search . '%')
                    ->orWhere('user_code', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%');
            });
        }

        $students = $studentsQuery
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(30)
            ->appends([
                'batch_id' => $selectedBatchId,
                'semester_id' => $selectedSemesterId,
                'program_id' => $selectedProgramId,
                'campus_id' => $selectedCampusId,
                'search' => $search,
            ]);

        $offeredMdcCourses = $this->resolveOfferedMdcCoursesForBatchSemester(
            $selectedBatchId,
            $selectedSemesterId,
            $selectedProgramId,
            $selectedCampusId,
        );

        $mdcOptionsByStudent = [];
        $currentMdcByStudent = [];

        foreach ($students as $student) {
            $studentId = (int) ($student->id ?? 0);
            $semesterId = (int) ($student->activeSemesterConfig->semester_id ?? 0);

            if ($studentId <= 0 || $semesterId <= 0) {
                $mdcOptionsByStudent[$studentId] = [];
                $currentMdcByStudent[$studentId] = null;
                continue;
            }

            $mdcOptionsByStudent[$studentId] = $this->resolveAvailableMdcCoursesForStudent($student, $semesterId);
            $currentMdcByStudent[$studentId] = $this->resolveCurrentMdcEnrollmentForStudent($student, $semesterId);
        }

        return view('admin.itcell.student-mdc-selection', [
            'batches' => $batches,
            'semesters' => $semesters,
            'campuses' => $campuses,
            'enrolledPrograms' => $enrolledPrograms,
            'selectedBatchId' => $selectedBatchId,
            'selectedSemesterId' => $selectedSemesterId,
            'selectedProgramId' => $selectedProgramId,
            'selectedCampusId' => $selectedCampusId,
            'search' => $search,
            'students' => $students,
            'offeredMdcCourses' => $offeredMdcCourses,
            'mdcOptionsByStudent' => $mdcOptionsByStudent,
            'currentMdcByStudent' => $currentMdcByStudent,
        ]);
    }

    public function studentMdcSelectionExport(Request $request)
    {
        $batchId = (int) $request->input('batch_id', 0);
        $semesterId = (int) $request->input('semester_id', 0);
        $programId = (int) $request->input('program_id', 0);
        $campusId = (int) $request->input('campus_id', 0);

        if ($batchId <= 0 || $semesterId <= 0) {
            return back()->with('error', 'Please select both batch and semester before exporting MDC offered list.');
        }

        $offeredMdcCourses = $this->resolveOfferedMdcCoursesForBatchSemester($batchId, $semesterId, $programId, $campusId);
        if (empty($offeredMdcCourses)) {
            return back()->with('error', 'No MDC offered records found to export for the selected filters.');
        }

        $batchName = (string) (BatchMaster::query()->where('id', $batchId)->value('batch_name') ?? $batchId);
        $semesterTitle = (string) (Semester::query()->where('id', $semesterId)->value('title') ?? ('Semester ' . $semesterId));

        $filename = 'mdc-offered-batch-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $batchName)
            . '-semester-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $semesterTitle)
            . '-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ];

        $callback = function () use ($offeredMdcCourses, $batchName, $semesterTitle) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for proper Excel compatibility.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Batch', 'Semester', 'Course Code', 'Course Title', 'Offered Department', 'Offered Programs']);

            foreach ($offeredMdcCourses as $course) {
                fputcsv($handle, [
                    $batchName,
                    $semesterTitle,
                    (string) ($course['course_code'] ?? ''),
                    (string) ($course['course_title'] ?? ''),
                    (string) ($course['offered_department'] ?? ''),
                    implode(', ', (array) ($course['offered_programs'] ?? [])),
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
    /*
    public function studentRosterEngineIndex(Request $request, StudentRosterEngine $studentRosterEngine)
    {
        $batches = BatchMaster::query()->orderByDesc('batch_name')->get(['id', 'batch_name']);
        $semesters = Semester::query()->orderBy('id')->get(['id', 'title']);

        $selectedBatchId = max(0, (int) $request->input('batch_id', 0));
        $selectedSemesterId = max(0, (int) $request->input('semester_id', 0));
        $selectedCurriculumRowId = max(0, (int) $request->input('curriculum_row_id', 0));
        $teachingGroupId = max(0, (int) $request->input('teaching_group_id', 0));
        $teachingAssignmentId = max(0, (int) $request->input('teaching_assignment_id', 0));

        $curriculumRows = collect();
        $selectedCurriculumRow = null;
        $resolvedRoster = collect();
        $resolvedProgramStats = collect();
        $rosterExclusionReasons = collect();
        $rosterExcludedStudents = collect();
        $rosterContext = [];
        $rosterExclusionReasons = collect();
        $rosterContext = [];
        $resolvedProgramStats = collect();
        $dashboardProgramStats = collect();
        $droppedRosterStudents = collect();
        $rosterContext = [];

        if ($selectedBatchId > 0) {
            $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
            $courseTable = (new ProgramCourseMaster())->getTable();

            $curriculumQuery = DB::table($curriculumTable . ' as ce')
                ->join($courseTable . ' as pcm', 'pcm.id', '=', 'ce.course_id')
                ->leftJoin('batch_masters as bm', 'bm.id', '=', 'ce.batch')
                ->leftJoin('subject_has_student_progams as shp', 'shp.id', '=', 'ce.program_combo_refid')
                ->leftJoin('student_program as sp', 'sp.id', '=', 'shp.student_program_id')
                ->leftJoin('academic_pathway_masters as ap', 'ap.id', '=', 'ce.academic_pathway_id')
                ->leftJoin('degree_track_masters as dt', 'dt.id', '=', 'ce.degree_track_id')
                ->leftJoin('subjects as od', 'od.id', '=', 'ce.offering_dept')
                ->where('ce.batch', $selectedBatchId);

            if ($selectedSemesterId > 0) {
                $curriculumQuery->where('ce.semester', $selectedSemesterId);
            }

            if (Schema::hasColumn($curriculumTable, 'deleted_at')) {
                $curriculumQuery->whereNull('ce.deleted_at');
            }

            if (Schema::hasColumn($curriculumTable, 'is_active')) {
                $curriculumQuery->where('ce.is_active', 1);
            }

            if (Schema::hasColumn($courseTable, 'deleted_at')) {
                $curriculumQuery->whereNull('pcm.deleted_at');
            }

            $curriculumRows = $curriculumQuery
                ->select([
                    'ce.id as curriculum_row_id',
                    'ce.program_combo_refid',
                    'ce.batch',
                    'bm.batch_name',
                    'ce.semester',
                    'ce.course_id',
                    'ce.offering_dept',
                    'ce.delivery_category',
                    'ce.course_type',
                    'pcm.course_code',
                    'pcm.course_title',
                    'shp.program_type',
                    'sp.code as program_code',
                    'sp.name as program_name',
                    'ap.name as pathway_name',
                    'dt.name as degree_track_name',
                    'od.title as offering_department_name',
                ])
                ->orderBy('ce.semester')
                ->orderBy('pcm.course_code')
                ->orderBy('ce.id')
                ->get();

            if ($selectedCurriculumRowId > 0) {
                $selectedCurriculumRow = $curriculumRows->first(function ($row) use ($selectedCurriculumRowId) {
                    return (int) ($row->curriculum_row_id ?? 0) === $selectedCurriculumRowId;
                });

                if (!$selectedCurriculumRow) {
                    $selectedCurriculumRow = $curriculumQuery
                        ->where('ce.id', $selectedCurriculumRowId)
                        ->select([
                            'ce.id as curriculum_row_id',
                            'ce.program_combo_refid',
                            'ce.batch',
                            'bm.batch_name',
                            'ce.semester',
                            'ce.course_id',
                            'ce.offering_dept',
                            'ce.delivery_category',
                            'ce.course_type',
                            'pcm.course_code',
                            'pcm.course_title',
                            'shp.program_type',
                            'sp.code as program_code',
                            'sp.name as program_name',
                            'ap.name as pathway_name',
                            'dt.name as degree_track_name',
                            'od.title as offering_department_name',
                        ])
                        ->first();
                }

                if ($selectedCurriculumRow) {
                    $rosterContext = [
                        'subject_id' => (int) ($selectedCurriculumRow->offering_dept ?? 0),
                        'batch_id' => (int) ($selectedCurriculumRow->batch ?? $selectedBatchId),
                        'semester_id' => (int) ($selectedCurriculumRow->semester ?? $selectedSemesterId),
                        'program_type' => (string) ($selectedCurriculumRow->program_type ?? ''),
                        'delivery_type' => (string) ($selectedCurriculumRow->delivery_category ?? ''),
                        'selection_type' => (string) ($selectedCurriculumRow->course_type ?? ''),
                        'teaching_group_id' => $teachingGroupId,
                        'teaching_assignment_id' => $teachingAssignmentId,
                    ];

                    $courseContext = (object) [
                        'id' => (int) ($selectedCurriculumRow->course_id ?? 0),
                        'delivery_type' => strtoupper(trim((string) ($selectedCurriculumRow->delivery_category ?? ''))),
                        'selection_type' => strtoupper(trim((string) ($selectedCurriculumRow->course_type ?? ''))),
                        'semester_id' => (int) ($selectedCurriculumRow->semester ?? $selectedSemesterId),
                        'batch_id' => (int) ($selectedCurriculumRow->batch ?? $selectedBatchId),
                    ];

                    $resolvedStudents = $studentRosterEngine->getStudentsForCourse($courseContext, $rosterContext)->values();

                    $pathwayNameMap = AcademicPathwayMaster::query()->pluck('name', 'id');
                    $degreeTrackNameMap = DegreeTrackMaster::query()->pluck('name', 'id');

                    $resolvedRoster = $resolvedStudents->map(function ($student) use ($courseContext, $pathwayNameMap, $degreeTrackNameMap, $rosterContext) {
                        $pathwayId = (int) ($student->academic_pathway_id ?? 0);
                        $degreeTrackId = (int) ($student->degree_track_id ?? 0);

                        return [
                            'student_id' => (int) ($student->id ?? 0),
                            'course_id' => (int) ($courseContext->id ?? 0),
                            'program_id' => (int) ($student->new_program_id ?? 0),
                            'batch_id' => (int) ($student->batch ?? 0),
                            'semester_id' => (int) ($courseContext->semester_id ?? 0),
                            'academic_pathway_id' => $pathwayId,
                            'academic_pathway' => (string) ($pathwayNameMap[$pathwayId] ?? ''),
                            'degree_track_id' => $degreeTrackId,
                            'degree_track' => (string) ($degreeTrackNameMap[$degreeTrackId] ?? ''),
                            'specialization_id' => null,
                            'delivery_type' => (string) ($courseContext->delivery_type ?? ''),
                            'selection_type' => (string) ($courseContext->selection_type ?? ''),
                            'teaching_group_id' => (int) ($rosterContext['teaching_group_id'] ?? 0),
                            'teaching_assignment_id' => (int) ($rosterContext['teaching_assignment_id'] ?? 0),
                            'roll_no' => (string) ($student->roll_no ?? ''),
                            'register_no' => (string) ($student->register_no ?? ''),
                            'student_name' => trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')),
                        ];
                    })->values();

                    if ($resolvedRoster->isNotEmpty()) {
                        $resolvedProgramIds = $resolvedRoster
                            ->pluck('program_id')
                            ->map(fn($id) => (int) $id)
                            ->filter(fn($id) => $id > 0)
                            ->unique()
                            ->values();

                        $programMap = StudentProgram::query()
                            ->whereIn('id', $resolvedProgramIds->all())
                            ->get(['id', 'code', 'name'])
                            ->keyBy('id');

                        $resolvedProgramStats = $resolvedRoster
                            ->groupBy(fn($student) => (int) ($student['program_id'] ?? 0))
                            ->map(function ($students, $programId) use ($programMap) {
                                $program = $programMap->get((int) $programId);
                                $programCode = trim((string) (optional($program)->code ?? '')) ?: 'N/A';
                                $programName = trim((string) (optional($program)->name ?? '')) ?: 'Unknown Program';

                                return [
                                    'program_id' => (int) $programId,
                                    'program_code' => $programCode,
                                    'program_name' => $programName,
                                    'student_count' => $students->count(),
                                ];
                            })
                            ->sortByDesc('student_count')
                            ->values();
                    }

                    $selectedCourseId = (int) ($selectedCurriculumRow->course_id ?? 0);
                    $selectedBatch = (int) ($selectedCurriculumRow->batch ?? $selectedBatchId);
                    $selectedSemester = (int) ($selectedCurriculumRow->semester ?? $selectedSemesterId);

                    $relevantCurriculumRows = $curriculumRows->filter(function ($row) use ($selectedCourseId, $selectedBatch, $selectedSemester) {
                        return (int) ($row->course_id ?? 0) === $selectedCourseId
                            && (int) ($row->batch ?? 0) === $selectedBatch
                            && (int) ($row->semester ?? 0) === $selectedSemester;
                    })->values();

                    $comparisonComboIds = $relevantCurriculumRows
                        ->pluck('program_combo_refid')
                        ->map(fn($id) => (int) $id)
                        ->filter(fn($id) => $id > 0)
                        ->unique()
                        ->values();

                    if ($comparisonComboIds->isNotEmpty()) {
                        $comparisonProgramIds = SubjectHasStudentProgam::query()
                            ->whereIn('id', $comparisonComboIds->all())
                            ->pluck('student_program_id')
                            ->map(fn($id) => (int) $id)
                            ->filter(fn($id) => $id > 0)
                            ->unique()
                            ->values();

                        if ($comparisonProgramIds->isNotEmpty()) {
                            $dashboardEligibleQuery = StudentMaster::query()
                                ->where('batch', $selectedBatch)
                                ->whereIn('new_program_id', $comparisonProgramIds->all());

                            if (Schema::hasColumn('student_masters', 'is_left')) {
                                $dashboardEligibleQuery->where('is_left', 0);
                            }

                            $dashboardEligibleStudents = $dashboardEligibleQuery
                                ->get([
                                    'id',
                                    'roll_no',
                                    'register_no',
                                    'first_name',
                                    'last_name',
                                    'new_program_id',
                                ])
                                ->map(function ($student) {
                                    return [
                                        'student_id' => (int) ($student->id ?? 0),
                                        'roll_no' => (string) ($student->roll_no ?? ''),
                                        'register_no' => (string) ($student->register_no ?? ''),
                                        'student_name' => trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')),
                                        'program_id' => (int) ($student->new_program_id ?? 0),
                                    ];
                                })
                                ->values();

                            $allProgramIds = $dashboardEligibleStudents->pluck('program_id')
                                ->merge($resolvedRoster->pluck('program_id')->map(fn($id) => (int) $id))
                                ->map(fn($id) => (int) $id)
                                ->filter(fn($id) => $id > 0)
                                ->unique()
                                ->values();

                            $programMap = StudentProgram::query()
                                ->whereIn('id', $allProgramIds->all())
                                ->get(['id', 'code', 'name'])
                                ->keyBy('id');

                            $resolvedCountsByProgram = $resolvedRoster
                                ->groupBy(fn($row) => (int) ($row['program_id'] ?? 0))
                                ->map(fn($rows) => $rows->count());

                            $expectedCountsByProgram = $dashboardEligibleStudents
                                ->groupBy(fn($row) => (int) ($row['program_id'] ?? 0))
                                ->map(fn($rows) => $rows->count());

                            $dashboardProgramStats = $allProgramIds
                                ->map(function ($programId) use ($programMap, $expectedCountsByProgram, $resolvedCountsByProgram) {
                                    $program = $programMap->get((int) $programId);
                                    $expectedCount = (int) ($expectedCountsByProgram->get((int) $programId) ?? 0);
                                    $resolvedCount = (int) ($resolvedCountsByProgram->get((int) $programId) ?? 0);

                                    return [
                                        'program_id' => (int) $programId,
                                        'program_code' => trim((string) (optional($program)->code ?? '')) ?: 'N/A',
                                        'program_name' => trim((string) (optional($program)->name ?? '')) ?: 'Unknown Program',
                                        'dashboard_count' => $expectedCount,
                                        'roster_count' => $resolvedCount,
                                        'dropped_count' => max(0, $expectedCount - $resolvedCount),
                                    ];
                                })
                                ->sortByDesc('dashboard_count')
                                ->values();

                            $resolvedStudentIds = $resolvedRoster
                                ->pluck('student_id')
                                ->map(fn($id) => (int) $id)
                                ->filter(fn($id) => $id > 0)
                                ->unique();

                            $droppedRows = $dashboardEligibleStudents
                                ->filter(fn($student) => !$resolvedStudentIds->contains((int) ($student['student_id'] ?? 0)))
                                ->values();

                            $droppedStudentIds = $droppedRows
                                ->pluck('student_id')
                                ->map(fn($id) => (int) $id)
                                ->filter(fn($id) => $id > 0)
                                ->unique()
                                ->values();

                            $hasEnrollmentIds = collect();
                            if ($droppedStudentIds->isNotEmpty() && $selectedCourseId > 0 && $selectedSemester > 0) {
                                $enrollmentQuery = StudentCourseInfo::query()
                                    ->whereIn('student_id', $droppedStudentIds->all())
                                    ->where('course_id', $selectedCourseId)
                                    ->where('semester', $selectedSemester);

                                if (Schema::hasColumn('student_course_infos', 'is_deleted')) {
                                    $enrollmentQuery->where('is_deleted', 0);
                                }

                                $hasEnrollmentIds = $enrollmentQuery
                                    ->pluck('student_id')
                                    ->map(fn($id) => (int) $id)
                                    ->filter(fn($id) => $id > 0)
                                    ->unique()
                                    ->values();
                            }

                            $droppedRosterStudents = $droppedRows
                                ->map(function ($student) use ($programMap, $hasEnrollmentIds) {
                                    $studentId = (int) ($student['student_id'] ?? 0);
                                    $programId = (int) ($student['program_id'] ?? 0);
                                    $program = $programMap->get($programId);
                                    $hasEnrollment = $hasEnrollmentIds->contains($studentId);

                                    return [
                                        'student_id' => $studentId,
                                        'roll_no' => (string) ($student['roll_no'] ?? ''),
                                        'register_no' => (string) ($student['register_no'] ?? ''),
                                        'student_name' => (string) ($student['student_name'] ?? ''),
                                        'program_id' => $programId,
                                        'program_code' => trim((string) (optional($program)->code ?? '')) ?: 'N/A',
                                        'program_name' => trim((string) (optional($program)->name ?? '')) ?: 'Unknown Program',
                                        'has_course_enrollment' => $hasEnrollment,
                                        'proof_note' => $hasEnrollment
                                            ? 'Dropped by roster rule after enrollment match'
                                            : 'Missing student_course_infos entry for selected course+semester',
                                    ];
                                })
                                ->sortBy('program_code')
                                ->values();
                        }
                    }
                }
            }
        }

        return view('admin.itcell.student-roster-engine', [
            'batches' => $batches,
            'semesters' => $semesters,
            'selectedBatchId' => $selectedBatchId,
            'selectedSemesterId' => $selectedSemesterId,
            'selectedCurriculumRowId' => $selectedCurriculumRowId,
            'teachingGroupId' => $teachingGroupId,
            'teachingAssignmentId' => $teachingAssignmentId,
            'curriculumRows' => $curriculumRows,
            'selectedCurriculumRow' => $selectedCurriculumRow,
            'resolvedRoster' => $resolvedRoster,
            'resolvedProgramStats' => $resolvedProgramStats,
            'dashboardProgramStats' => $dashboardProgramStats,
            'droppedRosterStudents' => $droppedRosterStudents,
            'rosterContext' => $rosterContext,
        ]);
    }
    */

    public function studentRosterEngineIndex(
        Request $request,
        StudentRosterEngine $studentRosterEngine
    ) {
        /*
    |--------------------------------------------------------------------------
    | 1. Basic filters
    |--------------------------------------------------------------------------
    */

        $batchId = (int) $request->input('batch_id', 0);
        $semesterId = (int) $request->input('semester_id', 0);
        $curriculumRowId = (int) $request->input('curriculum_row_id', 0);

        /*
    |--------------------------------------------------------------------------
    | 2. Master data
    |--------------------------------------------------------------------------
    */

        $batches = BatchMaster::query()
            ->orderByDesc('batch_name')
            ->get(['id', 'batch_name']);

        $pathways = AcademicPathwayMaster::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $degreeTracks = DegreeTrackMaster::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $semesters = Semester::query()
            ->orderBy('id')
            ->get(['id', 'title']);

        /*
    |--------------------------------------------------------------------------
    | 3. Defaults
    |--------------------------------------------------------------------------
    */

        $curriculumRows = collect();
        $selectedCurriculumRow = null;
        $resolvedRoster = collect();
        $resolvedProgramStats = collect();
        $rosterExclusionReasons = collect();
        $rosterExcludedStudents = collect();
        $rosterContext = [];

        /*
    |--------------------------------------------------------------------------
    | 4. Load courses offered for Batch + Semester
    |--------------------------------------------------------------------------
    */

        if ($batchId > 0) {

            $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
            $courseTable = (new ProgramCourseMaster())->getTable();

            $query = DB::table($curriculumTable . ' as ce')
                ->join(
                    $courseTable . ' as pcm',
                    'pcm.id',
                    '=',
                    'ce.course_id'
                )

                ->leftJoin(
                    'batch_masters as bm',
                    'bm.id',
                    '=',
                    'ce.batch'
                )

                ->leftJoin(
                    'subject_type_masters as stm',
                    'stm.id',
                    '=',
                    'pcm.course_type'
                )

                ->leftJoin(
                    'subject_has_student_progams as shp',
                    'shp.id',
                    '=',
                    'ce.program_combo_refid'
                )

                ->leftJoin(
                    'student_program as sp',
                    'sp.id',
                    '=',
                    'shp.student_program_id'
                )

                ->leftJoin(
                    'academic_pathway_masters as ap',
                    'ap.id',
                    '=',
                    'ce.academic_pathway_id'
                )

                ->leftJoin(
                    'degree_track_masters as dt',
                    'dt.id',
                    '=',
                    'ce.degree_track_id'
                )

                ->leftJoin(
                    'subjects as od',
                    'od.id',
                    '=',
                    'ce.offering_dept'
                )

                ->where(
                    'ce.batch',
                    $batchId
                );

            if ($semesterId > 0) {
                $query->where(
                    'ce.semester',
                    $semesterId
                );
            }

            if (Schema::hasColumn($curriculumTable, 'deleted_at')) {
                $query->whereNull('ce.deleted_at');
            }

            if (Schema::hasColumn($curriculumTable, 'is_active')) {
                $query->where('ce.is_active', 1);
            }

            if (Schema::hasColumn($courseTable, 'deleted_at')) {
                $query->whereNull('pcm.deleted_at');
            }

            $curriculumRows = $query
                ->select([
                    'ce.id as curriculum_row_id',

                    'ce.batch',
                    'ce.semester',
                    'ce.course_id',

                    'ce.program_combo_refid',

                    'ce.academic_pathway_id',
                    'ce.degree_track_id',

                    'ce.offering_dept',

                    'ce.delivery_category',
                    'ce.course_type',

                    'pcm.course_code',
                    'pcm.course_title',
                    'stm.title as course_type_name',

                    'bm.batch_name',

                    'shp.program_type',

                    'sp.code as program_code',
                    'sp.name as program_name',

                    'ap.name as pathway_name',
                    'dt.name as degree_track_name',

                    'od.title as offering_department_name',
                ])

                ->orderBy('ce.semester')
                ->orderBy('pcm.course_code')
                ->orderBy('ce.id')

                ->get();
        }

        /*
    |--------------------------------------------------------------------------
    | 5. Selected course
    |--------------------------------------------------------------------------
    */

        if ($curriculumRowId > 0) {

            $selectedCurriculumRow = $curriculumRows
                ->first(function ($row) use ($curriculumRowId) {

                    return (int) $row->curriculum_row_id
                        === $curriculumRowId;
                });
        }

        /*
    |--------------------------------------------------------------------------
    | 6. RUN STUDENT ROSTER ENGINE
    |--------------------------------------------------------------------------
    */

        if ($selectedCurriculumRow) {

            $decisionStartId = 0;
            if (Schema::hasTable('student_roster_rule_results')) {
                $decisionStartId = (int) (DB::table('student_roster_rule_results')->max('id') ?? 0);
            }

            $courseContext = [

                'id' =>
                (int) $selectedCurriculumRow->course_id,

                'curriculum_row_id' =>
                (int) $selectedCurriculumRow->curriculum_row_id,

                'course_id' =>
                (int) $selectedCurriculumRow->course_id,

                'batch_id' =>
                (int) $selectedCurriculumRow->batch,

                'semester_id' =>
                (int) $selectedCurriculumRow->semester,

                'academic_pathway_id' =>
                (int) $selectedCurriculumRow->academic_pathway_id,

                'degree_track_id' =>
                (int) $selectedCurriculumRow->degree_track_id,

                'program_combo_refid' =>
                (int) $selectedCurriculumRow->program_combo_refid,

                'program_type' =>
                strtoupper(
                    trim(
                        (string) $selectedCurriculumRow->program_type
                    )
                ),

                'delivery_type' =>
                strtoupper(
                    trim(
                        (string) $selectedCurriculumRow->delivery_category
                    )
                ),

                'selection_type' =>
                strtoupper(
                    trim(
                        (string) $selectedCurriculumRow->course_type
                    )
                ),

                'offering_dept' =>
                (int) $selectedCurriculumRow->offering_dept,
            ];

            $rosterContext = $courseContext;

            /*
         * The engine is now the SINGLE source of truth
         * for deciding which students attend this course.
         */

            $resolvedStudents =
                $studentRosterEngine
                ->getStudentsForCourse($courseContext, $courseContext)
                ->values();

            $resolvedProgramIds = $resolvedStudents
                ->pluck('new_program_id')
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $id > 0)
                ->unique()
                ->values();

            $programMap = StudentProgram::query()
                ->whereIn('id', $resolvedProgramIds->all())
                ->get(['id', 'code', 'name'])
                ->keyBy('id');

            $resolvedBatchIds = $resolvedStudents
                ->pluck('batch')
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $id > 0)
                ->unique()
                ->values();

            $batchNameMap = BatchMaster::query()
                ->whereIn('id', $resolvedBatchIds->all())
                ->pluck('batch_name', 'id');

            $pathwayNameMap = AcademicPathwayMaster::query()->pluck('name', 'id');
            $degreeTrackNameMap = DegreeTrackMaster::query()->pluck('name', 'id');

            $resolvedRoster = $resolvedStudents
                ->map(function ($student) use ($courseContext, $pathwayNameMap, $degreeTrackNameMap, $programMap, $batchNameMap) {
                    $pathwayId = (int) ($student->academic_pathway_id ?? 0);
                    $degreeTrackId = (int) ($student->degree_track_id ?? 0);
                    $programId = (int) ($student->new_program_id ?? 0);
                    $program = $programMap->get($programId);
                    $batchId = (int) ($student->batch ?? 0);

                    return [
                        'student_id' => (int) ($student->id ?? 0),
                        'student_name' => trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')),
                        'roll_no' => (string) ($student->roll_no ?? ''),
                        'register_no' => (string) ($student->register_no ?? ''),
                        'program_id' => $programId,
                        'program_code' => trim((string) (optional($program)->code ?? '')),
                        'program_name' => trim((string) (optional($program)->name ?? '')),
                        'batch_id' => $batchId,
                        'batch_name' => (string) ($batchNameMap[$batchId] ?? ''),
                        'semester_id' => (int) ($courseContext['semester_id'] ?? 0),
                        'academic_pathway_id' => $pathwayId,
                        'academic_pathway' => (string) ($pathwayNameMap[$pathwayId] ?? '-'),
                        'degree_track_id' => $degreeTrackId,
                        'degree_track' => (string) ($degreeTrackNameMap[$degreeTrackId] ?? '-'),
                        'delivery_type' => (string) ($courseContext['delivery_type'] ?? ''),
                        'selection_type' => (string) ($courseContext['selection_type'] ?? ''),
                    ];
                })
                ->values();

            $resolvedProgramStats = $resolvedRoster
                ->groupBy(function ($row) {
                    return (int) ($row['program_id'] ?? 0);
                })
                ->map(function ($rows) {
                    $first = $rows->first();

                    return [
                        'program_id' => (int) ($first['program_id'] ?? 0),
                        'program_code' => trim((string) ($first['program_code'] ?? '')) ?: 'N/A',
                        'program_name' => trim((string) ($first['program_name'] ?? '')) ?: 'Unknown Program',
                        'student_count' => $rows->count(),
                    ];
                })
                ->sortBy('program_code')
                ->values();

            $deliveryScopedProgramIds = collect();
            $deliveryType = strtoupper(trim((string) ($courseContext['delivery_type'] ?? '')));

            if (
                in_array($deliveryType, ['COMBO1', 'COMBO2'], true)
                && Schema::hasTable('subject_has_student_progams')
                && Schema::hasTable('std_prog_combo_maps')
            ) {
                $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();

                $deliveryScopeQuery = DB::table($curriculumTable . ' as ce')
                    ->join('subject_has_student_progams as shp', 'shp.id', '=', 'ce.program_combo_refid')
                    ->join('std_prog_combo_maps as spcm', 'spcm.student_program_id', '=', 'shp.student_program_id')
                    ->where('ce.course_id', (int) ($courseContext['course_id'] ?? 0))
                    ->whereRaw('UPPER(TRIM(COALESCE(ce.delivery_category, ""))) = ?', [$deliveryType]);

                if (Schema::hasColumn($curriculumTable, 'batch') && (int) ($courseContext['batch_id'] ?? 0) > 0) {
                    $deliveryScopeQuery->where('ce.batch', (int) $courseContext['batch_id']);
                }

                if (Schema::hasColumn($curriculumTable, 'semester') && (int) ($courseContext['semester_id'] ?? 0) > 0) {
                    $deliveryScopeQuery->where('ce.semester', (int) $courseContext['semester_id']);
                }

                if (Schema::hasColumn($curriculumTable, 'is_active')) {
                    $deliveryScopeQuery->where('ce.is_active', 1);
                }

                if (Schema::hasColumn($curriculumTable, 'deleted_at')) {
                    $deliveryScopeQuery->whereNull('ce.deleted_at');
                }

                if (Schema::hasColumn('subject_has_student_progams', 'deleted_at')) {
                    $deliveryScopeQuery->whereNull('shp.deleted_at');
                }

                if ($deliveryType === 'COMBO1') {
                    $deliveryScopeQuery->whereColumn('spcm.combo_id_1', 'shp.subject_id');
                } else {
                    $deliveryScopeQuery->whereColumn('spcm.combo_id_2', 'shp.subject_id');
                }

                $deliveryScopedProgramIds = $deliveryScopeQuery
                    ->pluck('shp.student_program_id')
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values();
            }

            if (Schema::hasTable('student_roster_rule_results')) {
                $reasonQuery = DB::table('student_roster_rule_results as srr')
                    ->join('student_masters as sm', 'sm.id', '=', 'srr.student_id')
                    ->where('srr.id', '>', $decisionStartId)
                    ->where('srr.subject_course_id', (int) ($courseContext['course_id'] ?? 0))
                    ->where('srr.included', 0)
                    ->select([
                        'srr.reason_code',
                        DB::raw('COUNT(*) as total'),
                    ])
                    ->groupBy('srr.reason_code')
                    ->orderByDesc('total');

                if ($deliveryScopedProgramIds->isNotEmpty()) {
                    $reasonQuery->whereIn('sm.new_program_id', $deliveryScopedProgramIds->all());
                }

                $rosterExclusionReasons = $reasonQuery->get()->map(function ($row) {
                    return [
                        'reason_code' => (string) ($row->reason_code ?? 'UNKNOWN'),
                        'total' => (int) ($row->total ?? 0),
                    ];
                })->values();

                $rosterExcludedStudents = DB::table('student_roster_rule_results as srr')
                    ->join('student_masters as sm', 'sm.id', '=', 'srr.student_id')
                    ->leftJoin('student_program as sp', 'sp.id', '=', 'sm.new_program_id')
                    ->leftJoin('academic_pathway_masters as ap', 'ap.id', '=', 'sm.academic_pathway_id')
                    ->leftJoin('degree_track_masters as dt', 'dt.id', '=', 'sm.degree_track_id')
                    ->leftJoin('batch_masters as bm', 'bm.id', '=', 'sm.batch')
                    ->where('srr.id', '>', $decisionStartId)
                    ->where('srr.subject_course_id', (int) ($courseContext['course_id'] ?? 0))
                    ->where('srr.included', 0)
                    ->orderBy('srr.reason_code')
                    ->orderBy('sm.roll_no')
                    ->select([
                        'sm.id as student_id',
                        'sm.roll_no',
                        'sm.register_no',
                        'sm.first_name',
                        'sm.last_name',
                        'sp.code as program_code',
                        'sp.name as program_name',
                        'bm.batch_name',
                        'ap.name as pathway_name',
                        'dt.name as degree_track_name',
                        'srr.reason_code',
                        'srr.reason',
                    ])
                    ->when($deliveryScopedProgramIds->isNotEmpty(), function ($q) use ($deliveryScopedProgramIds) {
                        $q->whereIn('sm.new_program_id', $deliveryScopedProgramIds->all());
                    })
                    ->get()
                    ->map(function ($row) {
                        return [
                            'student_id' => (int) ($row->student_id ?? 0),
                            'student_name' => trim((string) ($row->first_name ?? '') . ' ' . (string) ($row->last_name ?? '')),
                            'roll_no' => (string) ($row->roll_no ?? ''),
                            'register_no' => (string) ($row->register_no ?? ''),
                            'program_code' => trim((string) ($row->program_code ?? '')),
                            'program_name' => trim((string) ($row->program_name ?? '')),
                            'batch_name' => trim((string) ($row->batch_name ?? '')),
                            'academic_pathway' => trim((string) ($row->pathway_name ?? '')),
                            'degree_track' => trim((string) ($row->degree_track_name ?? '')),
                            'reason_code' => (string) ($row->reason_code ?? 'UNKNOWN'),
                            'reason' => (string) ($row->reason ?? ''),
                        ];
                    })
                    ->values();
            }
        }

        /*
    |--------------------------------------------------------------------------
    | 7. Return view
    |--------------------------------------------------------------------------
    */

        return view(
            'admin.itcell.student-roster-engine',
            [

                'batches' =>
                $batches,

                'pathways' =>
                $pathways,

                'degreeTracks' =>
                $degreeTracks,

                'semesters' =>
                $semesters,

                'selectedBatchId' =>
                $batchId,

                'selectedSemesterId' =>
                $semesterId,

                'selectedCurriculumRowId' =>
                $curriculumRowId,

                'curriculumRows' =>
                $curriculumRows,

                'selectedCurriculumRow' =>
                $selectedCurriculumRow,

                'resolvedRoster' =>
                $resolvedRoster,

                'resolvedProgramStats' =>
                $resolvedProgramStats,

                'rosterContext' =>
                $rosterContext,

                'rosterExclusionReasons' =>
                $rosterExclusionReasons,

                'rosterExcludedStudents' =>
                $rosterExcludedStudents,
            ]
        );
    }

    public function fixNoAcademicPathwayInRoster(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer|exists:student_masters,id',
            'academic_pathway_id' => 'required|integer|exists:academic_pathway_masters,id',
            'degree_track_id' => 'nullable|integer|exists:degree_track_masters,id',
            'batch_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
            'curriculum_row_id' => 'nullable|integer',
            'teaching_group_id' => 'nullable|integer',
            'teaching_assignment_id' => 'nullable|integer',
        ]);

        $studentId = (int) $request->input('student_id');
        $selectedSemesterId = (int) $request->input('semester_id', 0);
        $semesterConfigCreated = false;
        $pathwayId = (int) $request->input('academic_pathway_id');

        $selectedPathway = AcademicPathwayMaster::query()
            ->where('id', $pathwayId)
            ->first(['id', 'name']);

        $pathwayNameUpper = strtoupper(trim((string) ($selectedPathway->name ?? '')));
        $isDualMajorPathway = str_contains($pathwayNameUpper, 'DUAL')
            && str_contains($pathwayNameUpper, 'MAJOR');

        $updateData = [
            'academic_pathway_id' => $pathwayId,
        ];

        if ($isDualMajorPathway) {
            $regularTrack = DegreeTrackMaster::query()
                ->whereRaw('UPPER(TRIM(COALESCE(name, ""))) = ?', ['REGULAR'])
                ->orWhereRaw('UPPER(TRIM(COALESCE(name, ""))) LIKE ?', ['%REGULAR%'])
                ->orderByRaw('CASE WHEN UPPER(TRIM(COALESCE(name, ""))) = "REGULAR" THEN 0 ELSE 1 END')
                ->orderBy('id')
                ->first(['id', 'name']);

            if (!$regularTrack) {
                $errorMessage = 'Dual Major pathway requires a Regular degree track, but no Regular track was found.';
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'message' => $errorMessage,
                    ], 422);
                }

                return redirect()->back()->with('error', $errorMessage);
            }

            $updateData['degree_track_id'] = (int) $regularTrack->id;
        } elseif ($request->filled('degree_track_id')) {
            $updateData['degree_track_id'] = (int) $request->input('degree_track_id');
        }

        DB::transaction(function () use ($studentId, $selectedSemesterId, $updateData, &$semesterConfigCreated) {
            StudentMaster::query()
                ->where('id', $studentId)
                ->update($updateData);

            if ($selectedSemesterId > 0) {
                $semesterConfigQuery = StudentSemesterConfig::query()
                    ->where('student_id', (string) $studentId)
                    ->where('semester_id', (string) $selectedSemesterId);

                if (!$semesterConfigQuery->exists()) {
                    StudentSemesterConfig::query()
                        ->where('student_id', (string) $studentId)
                        ->update(['current_semester' => 0]);

                    StudentSemesterConfig::query()->create([
                        'student_id' => (string) $studentId,
                        'semester_id' => (string) $selectedSemesterId,
                        'current_semester' => 1,
                    ]);

                    $semesterConfigCreated = true;
                }
            }
        });

        $redirectQuery = array_filter([
            'batch_id' => (int) $request->input('batch_id', 0),
            'semester_id' => (int) $request->input('semester_id', 0),
            'curriculum_row_id' => (int) $request->input('curriculum_row_id', 0),
            'teaching_group_id' => (int) $request->input('teaching_group_id', 0),
            'teaching_assignment_id' => (int) $request->input('teaching_assignment_id', 0),
        ], function ($value) {
            return !is_null($value) && (int) $value > 0;
        });

        $successMessage = $semesterConfigCreated
            ? 'Academic pathway and degree track updated. Missing semester config was added and activated. Please resolve roster again.'
            : 'Academic pathway and degree track updated for the selected student. Please resolve roster again.';

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $successMessage,
                'redirect_url' => route('itcell.student-roster-engine.index', $redirectQuery),
            ]);
        }

        return redirect()
            ->route('itcell.student-roster-engine.index', $redirectQuery)
            ->with('success', $successMessage);
    }
    public function subjectProgramEnrollmentInspectorIndex(Request $request)
    {
        $batches = BatchMaster::query()->orderByDesc('batch_name')->get(['id', 'batch_name']);
        $departmentsQuery = DB::table('subjects as sub')
            ->select(['sub.id', 'sub.title', 'sub.code']);

        if (Schema::hasColumn('subjects', 'deleted_at')) {
            $departmentsQuery->whereNull('sub.deleted_at');
        }

        if (Schema::hasColumn('subjects', 'campus_id') && Schema::hasTable('campuses')) {
            $departmentsQuery
                ->leftJoin('campuses as campus', 'campus.id', '=', 'sub.campus_id')
                ->addSelect('campus.name as campus_name');
        }

        $departments = $departmentsQuery
            ->orderBy('sub.title')
            ->get();

        $selectedBatchId = max(0, (int) $request->input('batch_id', 0));
        $selectedDepartmentId = max(0, (int) $request->input('department_id', 0));

        $combinationRows = collect();
        $programSummaryRows = collect();
        $selectedDepartmentComboInsights = null;
        $totalEnrolledStudents = 0;
        $enrollmentByProgram = collect();

        if ($selectedBatchId > 0) {
            $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();

            $comboQuery = DB::table('subject_has_student_progams as shp')
                ->join('subjects as sub', 'sub.id', '=', 'shp.subject_id')
                ->join('student_program as sp', 'sp.id', '=', 'shp.student_program_id')
                ->where('shp.batch_id', $selectedBatchId);

            if (Schema::hasColumn('subjects', 'campus_id') && Schema::hasTable('campuses')) {
                $comboQuery->leftJoin('campuses as campus', 'campus.id', '=', 'sub.campus_id');
            }

            if ($selectedDepartmentId > 0) {
                $comboQuery->where('shp.subject_id', $selectedDepartmentId);
            }

            if (Schema::hasColumn('subject_has_student_progams', 'deleted_at')) {
                $comboQuery->whereNull('shp.deleted_at');
            }

            if (Schema::hasTable($curriculumTable)) {
                $comboQuery->leftJoin($curriculumTable . ' as ce', function ($join) use ($selectedBatchId, $curriculumTable) {
                    $join->on('ce.program_combo_refid', '=', 'shp.id')
                        ->where('ce.batch', '=', $selectedBatchId);

                    if (Schema::hasColumn($curriculumTable, 'deleted_at')) {
                        $join->whereNull('ce.deleted_at');
                    }

                    if (Schema::hasColumn($curriculumTable, 'is_active')) {
                        $join->where('ce.is_active', '=', 1);
                    }
                });
            }

            $combo1Aliases = [
                ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
                'CORE-A',
                'COREA',
                'MAJOR_COMBO1',
                'COMBO1F',
                'COMBO1-F',
                'COMBO1_F',
            ];
            $combo2Aliases = [
                ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2,
                'CORE-B',
                'COREB',
                'MAJOR_COMBO2',
                'COMBO2F',
                'COMBO2-F',
                'COMBO2_F',
            ];

            $combo1SqlList = "'" . implode("','", array_map(fn($v) => strtoupper(trim((string) $v)), $combo1Aliases)) . "'";
            $combo2SqlList = "'" . implode("','", array_map(fn($v) => strtoupper(trim((string) $v)), $combo2Aliases)) . "'";

            $combinationRows = $comboQuery
                ->select([
                    'shp.id as combo_id',
                    'shp.subject_id as department_id',
                    'sub.code as department_code',
                    'sub.title as department_name',
                    'campus.name as campus_name',
                    'shp.student_program_id as program_id',
                    'sp.code as program_code',
                    'sp.name as program_name',
                    'shp.program_type',
                ])
                ->selectRaw('COUNT(DISTINCT ce.id) as linked_curriculum_rows')
                ->selectRaw("SUM(CASE WHEN UPPER(TRIM(COALESCE(ce.delivery_category, ''))) IN ($combo1SqlList) THEN 1 ELSE 0 END) as combo1_rows")
                ->selectRaw("SUM(CASE WHEN UPPER(TRIM(COALESCE(ce.delivery_category, ''))) IN ($combo2SqlList) THEN 1 ELSE 0 END) as combo2_rows")
                ->groupBy('shp.id', 'shp.subject_id', 'sub.code', 'sub.title', 'campus.name', 'shp.student_program_id', 'sp.code', 'sp.name', 'shp.program_type')
                ->orderBy('sub.title')
                ->orderBy('sp.code')
                ->get()
                ->map(function ($row) {
                    $row->combo1_rows = (int) ($row->combo1_rows ?? 0);
                    $row->combo2_rows = (int) ($row->combo2_rows ?? 0);
                    $row->linked_curriculum_rows = (int) ($row->linked_curriculum_rows ?? 0);
                    return $row;
                })
                ->values();

            $programIds = $combinationRows->pluck('program_id')
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $id > 0)
                ->unique()
                ->values();

            // Batch-level enrollment map used by both COMBO1 and COMBO2 views.
            $batchEnrollmentQuery = StudentMaster::query()->where('batch', $selectedBatchId);

            if (Schema::hasColumn('student_masters', 'is_deleted')) {
                $batchEnrollmentQuery->where('is_deleted', 0);
            }

            if (Schema::hasColumn('student_masters', 'is_left')) {
                $batchEnrollmentQuery->where(function ($q) {
                    $q->whereNull('is_left')->orWhere('is_left', 0);
                });
            }

            $enrollmentByProgram = $batchEnrollmentQuery
                ->whereNotNull('new_program_id')
                ->selectRaw('new_program_id as program_id, COUNT(*) as students_count')
                ->groupBy('new_program_id')
                ->pluck('students_count', 'program_id');

            $totalEnrolledStudents = (int) $enrollmentByProgram->sum();

            if ($programIds->isNotEmpty()) {
                $enrollmentQuery = StudentMaster::query()
                    ->where('batch', $selectedBatchId)
                    ->whereIn('new_program_id', $programIds->all());

                if (Schema::hasColumn('student_masters', 'is_deleted')) {
                    $enrollmentQuery->where('is_deleted', 0);
                }

                if (Schema::hasColumn('student_masters', 'is_left')) {
                    $enrollmentQuery->where(function ($q) {
                        $q->whereNull('is_left')->orWhere('is_left', 0);
                    });
                }

                $enrollmentBySelectedPrograms = $enrollmentQuery
                    ->selectRaw('new_program_id as program_id, COUNT(*) as students_count')
                    ->groupBy('new_program_id')
                    ->pluck('students_count', 'program_id');

                $programSummaryRows = $combinationRows
                    ->groupBy(fn($row) => (int) ($row->program_id ?? 0))
                    ->map(function ($rows, $programId) use ($enrollmentBySelectedPrograms, $totalEnrolledStudents) {
                        $first = $rows->first();
                        $studentsCount = (int) ($enrollmentBySelectedPrograms[(int) $programId] ?? 0);
                        $combo1Offered = $rows->sum(fn($row) => (int) ($row->combo1_rows ?? 0));
                        $combo2Offered = $rows->sum(fn($row) => (int) ($row->combo2_rows ?? 0));

                        return [
                            'program_id' => (int) $programId,
                            'program_code' => (string) ($first->program_code ?? ''),
                            'program_name' => (string) ($first->program_name ?? ''),
                            'department_links' => $rows->pluck('department_id')->unique()->count(),
                            'combo1_rows' => $combo1Offered,
                            'combo2_rows' => $combo2Offered,
                            'students_count' => $studentsCount,
                            'enrollment_share' => $totalEnrolledStudents > 0
                                ? round(($studentsCount / $totalEnrolledStudents) * 100, 2)
                                : 0,
                        ];
                    })
                    ->sortByDesc('students_count')
                    ->values();
            }

            if ($selectedDepartmentId > 0) {
                // COMBO1 source: same as department dashboard -> combinations linked by subject_id + batch.
                $combo1Programs = $combinationRows
                    ->groupBy(fn($row) => (int) ($row->program_id ?? 0))
                    ->map(function ($programRows, $programId) use ($enrollmentByProgram) {
                        $first = $programRows->first();
                        return [
                            'program_id' => (int) $programId,
                            'program_code' => (string) ($first->program_code ?? ''),
                            'program_name' => (string) ($first->program_name ?? ''),
                            'students_count' => (int) ($enrollmentByProgram[(int) $programId] ?? 0),
                        ];
                    })
                    ->sortByDesc('students_count')
                    ->values();

                // COMBO2 source: programs whose combo map has selected department in combo_id_2 for the selected batch.
                $combo2Query = DB::table('subject_has_student_progams as shp')
                    ->join('std_prog_combo_maps as scm', 'scm.student_program_id', '=', 'shp.student_program_id')
                    ->join('student_program as sp', 'sp.id', '=', 'shp.student_program_id')
                    ->where('shp.batch_id', $selectedBatchId)
                    ->where('scm.combo_id_2', $selectedDepartmentId);

                if (Schema::hasColumn('subject_has_student_progams', 'deleted_at')) {
                    $combo2Query->whereNull('shp.deleted_at');
                }

                if (Schema::hasColumn('std_prog_combo_maps', 'deleted_at')) {
                    $combo2Query->whereNull('scm.deleted_at');
                }

                $combo2Programs = $combo2Query
                    ->select([
                        'sp.id as program_id',
                        'sp.code as program_code',
                        'sp.name as program_name',
                    ])
                    ->groupBy('sp.id', 'sp.code', 'sp.name')
                    ->orderBy('sp.code')
                    ->get()
                    ->map(function ($row) use ($enrollmentByProgram) {
                        return [
                            'program_id' => (int) ($row->program_id ?? 0),
                            'program_code' => (string) ($row->program_code ?? ''),
                            'program_name' => (string) ($row->program_name ?? ''),
                            'students_count' => (int) ($enrollmentByProgram[(int) ($row->program_id ?? 0)] ?? 0),
                        ];
                    })
                    ->sortByDesc('students_count')
                    ->values();

                $selectedDepartment = $departments->firstWhere('id', $selectedDepartmentId);
                $selectedBatchName = (string) optional($batches->firstWhere('id', $selectedBatchId))->batch_name;

                $combo1TotalStudents = (int) $combo1Programs->sum(fn($row) => (int) ($row['students_count'] ?? 0));
                $combo2TotalStudents = (int) $combo2Programs->sum(fn($row) => (int) ($row['students_count'] ?? 0));

                $uniqueProgramIds = $combo1Programs
                    ->pluck('program_id')
                    ->merge($combo2Programs->pluck('program_id'))
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values();

                $uniqueTotalStudents = (int) $uniqueProgramIds
                    ->sum(fn($programId) => (int) ($enrollmentByProgram[(int) $programId] ?? 0));

                $selectedDepartmentComboInsights = [
                    'department_name' => (string) ($selectedDepartment->title ?? $combinationRows->first()->department_name ?? ''),
                    'batch_name' => $selectedBatchName,
                    'combo1_programs' => $combo1Programs,
                    'combo2_programs' => $combo2Programs,
                    'combo1_total_students' => $combo1TotalStudents,
                    'combo2_total_students' => $combo2TotalStudents,
                    'unique_total_students' => $uniqueTotalStudents,
                ];
            }
        }

        return view('admin.itcell.subject-program-enrollment-inspector', [
            'batches' => $batches,
            'departments' => $departments,
            'selectedBatchId' => $selectedBatchId,
            'selectedDepartmentId' => $selectedDepartmentId,
            'combinationRows' => $combinationRows,
            'programSummaryRows' => $programSummaryRows,
            'selectedDepartmentComboInsights' => $selectedDepartmentComboInsights,
            'totalEnrolledStudents' => $totalEnrolledStudents,
        ]);
    }

    private function resolveOfferedMdcCoursesForBatchSemester(int $batchId, int $semesterId, int $programId = 0, int $campusId = 0): array
    {
        if ($batchId <= 0 || $semesterId <= 0 || !Schema::hasTable('curriculam_engine')) {
            return [];
        }

        $courseTable = (new ProgramCourseMaster())->getTable();

        $query = DB::table('curriculam_engine as ce')
            ->join($courseTable . ' as pcm', 'pcm.id', '=', 'ce.course_id')
            ->leftJoin('subject_type_masters as stm', 'stm.id', '=', 'pcm.course_type')
            ->leftJoin('subjects as sub', 'sub.id', '=', 'pcm.department')
            ->leftJoin('subject_has_student_progams as shsp', 'shsp.id', '=', 'ce.program_combo_refid')
            ->leftJoin('student_program as sp', 'sp.id', '=', 'shsp.student_program_id')
            ->where('ce.batch', $batchId)
            ->where('ce.semester', $semesterId);

        if (Schema::hasColumn('curriculam_engine', 'deleted_at')) {
            $query->whereNull('ce.deleted_at');
        }

        if (Schema::hasColumn('curriculam_engine', 'is_active')) {
            $query->where('ce.is_active', 1);
        }

        if (Schema::hasColumn('curriculam_engine', 'delivery_category')) {
            $query->whereRaw("UPPER(TRIM(COALESCE(ce.delivery_category, ''))) = ?", [ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE]);
        }

        if (Schema::hasColumn($courseTable, 'deleted_at')) {
            $query->whereNull('pcm.deleted_at');
        }

        if ($programId > 0) {
            $query->where('shsp.student_program_id', $programId);
        }

        if ($campusId > 0 && Schema::hasColumn('subject_has_student_progams', 'campus_id')) {
            $query->where('shsp.campus_id', $campusId);
        }

        $rows = $query
            ->select([
                'pcm.id as course_id',
                'pcm.course_code',
                'pcm.course_title',
                'stm.title as course_type_title',
                'sub.title as department_title',
                'sp.id as program_id',
                'sp.code as program_code',
                'sp.name as program_name',
            ])
            ->orderBy('pcm.course_title')
            ->get();

        $mdcRows = collect($rows)->filter(function ($row) {
            $typeTitle = strtoupper(trim((string) ($row->course_type_title ?? '')));
            $normalizedType = $typeTitle !== '' ? preg_replace('/\s.*/', '', $typeTitle) : '';
            $courseCode = strtoupper(trim((string) ($row->course_code ?? '')));

            return $normalizedType === ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE
                || str_contains($courseCode, ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE);
        });

        return $mdcRows
            ->groupBy(fn($row) => (int) ($row->course_id ?? 0))
            ->map(function ($group) {
                $first = $group->first();
                $offeredPrograms = $group
                    ->map(function ($row) {
                        $code = trim((string) ($row->program_code ?? ''));
                        $name = trim((string) ($row->program_name ?? ''));
                        return trim(($code !== '' ? $code . ' - ' : '') . $name);
                    })
                    ->filter(fn($label) => $label !== '')
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'course_id' => (int) ($first->course_id ?? 0),
                    'course_code' => (string) ($first->course_code ?? ''),
                    'course_title' => (string) ($first->course_title ?? ''),
                    'offered_department' => (string) ($first->department_title ?? ''),
                    'offered_programs' => $offeredPrograms,
                ];
            })
            ->sortBy(fn($row) => trim(($row['course_code'] ?? '') . ' ' . ($row['course_title'] ?? '')))
            ->values()
            ->all();
    }

    public function studentMdcSelectionStore(Request $request)
    {
        $courseTable = (new ProgramCourseMaster())->getTable();

        $validated = $request->validate([
            'batch_id' => 'nullable|integer|exists:batch_masters,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'mdc_course_id' => 'required|integer|exists:' . $courseTable . ',id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:student_masters,id',
        ]);

        try {
            $semesterId = (int) $validated['semester_id'];
            $selectedMdcCourseId = (int) $validated['mdc_course_id'];
            $selectedBatchId = (int) $validated['batch_id'];
            $studentIds = collect((array) $validated['student_ids'])
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $id > 0)
                ->unique()
                ->values();

            $summary = [
                'processed' => 0,
                'enrolled' => 0,
                'restored' => 0,
                'already' => 0,
                'skipped_semester' => 0,
                'replaced' => 0,
            ];

            DB::transaction(function () use ($studentIds, $selectedBatchId, $semesterId, $selectedMdcCourseId, &$summary) {
                $studentsQuery = StudentMaster::query()
                    ->with([
                        'batchmaster:id,batch_name',
                        'activeSemesterConfig:id,student_id,semester_id,current_semester',
                    ])
                    ->whereIn('id', $studentIds->all())
                    ->lockForUpdate();

                if ($selectedBatchId > 0) {
                    $studentsQuery->where('batch', $selectedBatchId);
                }

                $students = $studentsQuery->get();

                foreach ($students as $student) {
                    $summary['processed']++;

                    $activeSemesterId = (int) ($student->activeSemesterConfig->semester_id ?? 0);
                    if ($activeSemesterId !== $semesterId) {
                        $summary['skipped_semester']++;
                        continue;
                    }

                    $academicYear = (string) (optional($student->batchmaster)->batch_name ?: date('Y'));

                    // Rule: only one elective/MDC row per student per semester.
                    $replacedRows = StudentCourseInfo::query()
                        ->where('student_id', (int) $student->id)
                        ->where('semester', $semesterId)
                        ->where('is_elective', 1)
                        ->where('course_id', '!=', $selectedMdcCourseId)
                        ->delete();

                    if ($replacedRows > 0) {
                        $summary['replaced'] += (int) $replacedRows;
                    }

                    $existingSelectedCourse = StudentCourseInfo::withTrashed()
                        ->where('student_id', (int) $student->id)
                        ->where('course_id', $selectedMdcCourseId)
                        ->where('semester', $semesterId)
                        ->first();

                    if ($existingSelectedCourse) {
                        if (method_exists($existingSelectedCourse, 'trashed') && $existingSelectedCourse->trashed()) {
                            $existingSelectedCourse->restore();
                            $summary['restored']++;
                        } else {
                            $summary['already']++;
                        }

                        $existingSelectedCourse->update([
                            'campus_id' => (int) ($student->campus_id ?? 0),
                            'is_active' => 1,
                            'is_elective' => 1,
                            'academic_year' => $academicYear,
                        ]);
                    } else {
                        StudentCourseInfo::create([
                            'student_id' => (int) $student->id,
                            'course_id' => $selectedMdcCourseId,
                            'semester' => $semesterId,
                            'campus_id' => (int) ($student->campus_id ?? 0),
                            'is_active' => 1,
                            'academic_year' => $academicYear,
                            'is_elective' => 1,
                        ]);
                        $summary['enrolled']++;
                    }
                }
            });

            $message = 'Bulk MDC enrollment completed. '
                . $summary['enrolled'] . ' enrolled, '
                . $summary['restored'] . ' restored, '
                . $summary['already'] . ' already enrolled.';

            if ($summary['skipped_semester'] > 0) {
                $message .= ' Skipped ' . $summary['skipped_semester'] . ' student(s) due to semester mismatch.';
            }

            if ($summary['replaced'] > 0) {
                $message .= ' Replaced ' . $summary['replaced'] . ' previous elective/MDC row(s) to enforce one MDC per semester.';
            }

            if ($summary['processed'] === 0) {
                return back()->with('error', 'No matching students were processed for the selected filters.');
            }

            return back()->with('success', $message);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to update MDC selection. Please try again.');
        }
    }

    private function isMdcCourseMappedForStudent(StudentMaster $student, int $semesterId, int $courseId): bool
    {
        if ($semesterId <= 0 || $courseId <= 0 || empty($student->new_program_id) || empty($student->batch)) {
            return false;
        }

        if (!Schema::hasTable('subject_has_student_progams') || !Schema::hasTable('curriculam_engine')) {
            return false;
        }

        $combinationQuery = DB::table('subject_has_student_progams')
            ->where('student_program_id', (int) $student->new_program_id)
            ->where('batch_id', (int) $student->batch);

        if (Schema::hasColumn('subject_has_student_progams', 'campus_id') && (int) ($student->campus_id ?? 0) > 0) {
            $combinationQuery->where('campus_id', (int) $student->campus_id);
        }

        $combinationIds = $combinationQuery
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->values();

        if ($combinationIds->isEmpty()) {
            $fallbackComboIds = DB::table('subject_has_student_progams')
                ->where('student_program_id', (int) $student->new_program_id)
                ->where('batch_id', (int) $student->batch)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $id > 0)
                ->values();

            $combinationIds = $fallbackComboIds;
        }

        if ($combinationIds->isEmpty()) {
            return false;
        }

        $baseQuery = DB::table('curriculam_engine')
            ->whereIn('program_combo_refid', $combinationIds->all())
            ->where('batch', (int) $student->batch)
            ->where('semester', $semesterId)
            ->where('course_id', $courseId);

        if (Schema::hasColumn('curriculam_engine', 'deleted_at')) {
            $baseQuery->whereNull('deleted_at');
        }

        if (Schema::hasColumn('curriculam_engine', 'is_active')) {
            $baseQuery->where('is_active', 1);
        }

        $hasDeliveryCategoryColumn = Schema::hasColumn('curriculam_engine', 'delivery_category');
        if ($hasDeliveryCategoryColumn) {
            $mdcDeliveryQuery = clone $baseQuery;
            $mdcDeliveryQuery->whereRaw(
                "UPPER(TRIM(COALESCE(delivery_category, ''))) IN (?, ?, ?)",
                [
                    ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE,
                    'OPEN_CHOICE',
                    'OPEN CHOICE',
                ]
            );

            if ($mdcDeliveryQuery->exists()) {
                return true;
            }
        }

        // Fallback: if delivery category data is inconsistent/missing, accept only if
        // curriculum row exists and the course itself is tagged as MDC.
        if (!$baseQuery->exists()) {
            return false;
        }

        return $this->isMdcCourseId($courseId);
    }

    private function isMdcCourseId(int $courseId): bool
    {
        if ($courseId <= 0) {
            return false;
        }

        $course = ProgramCourseMaster::query()
            ->with(['coursetypemaster:id,title'])
            ->find($courseId);

        return $this->isMdcCourse($course);
    }

    private function resolveAvailableMdcCoursesForStudent(StudentMaster $student, int $semesterId): array
    {
        if ($semesterId <= 0 || empty($student->new_program_id) || empty($student->batch)) {
            return [];
        }

        $combinationQuery = SubjectHasStudentProgam::query()
            ->where('student_program_id', (int) $student->new_program_id)
            ->where('batch_id', (int) $student->batch);

        if (Schema::hasColumn('subject_has_student_progams', 'campus_id') && (int) ($student->campus_id ?? 0) > 0) {
            $combinationQuery->where('campus_id', (int) $student->campus_id);
        }

        $combination = $combinationQuery->orderByDesc('id')->first(['id']);

        if (!$combination) {
            $combination = SubjectHasStudentProgam::query()
                ->where('student_program_id', (int) $student->new_program_id)
                ->where('batch_id', (int) $student->batch)
                ->orderByDesc('id')
                ->first(['id']);
        }

        if (!$combination) {
            return [];
        }

        $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();

        $courseIdsQuery = ProgramWiseSemesterCourse::query()
            ->where('program_combo_refid', (int) $combination->id)
            ->where('batch', (int) $student->batch)
            ->where('semester', $semesterId);

        if (Schema::hasColumn($curriculumTable, 'is_active')) {
            $courseIdsQuery->where('is_active', 1);
        }

        if (Schema::hasColumn($curriculumTable, 'delivery_category')) {
            $courseIdsQuery->whereRaw("UPPER(TRIM(COALESCE(delivery_category, ''))) = ?", [ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE]);
        }

        if (Schema::hasColumn($curriculumTable, 'academic_pathway_id')) {
            $pathwayId = (int) ($student->academic_pathway_id ?? 0);
            if ($pathwayId > 0) {
                $courseIdsQuery->where('academic_pathway_id', $pathwayId);
            } else {
                $courseIdsQuery->whereNull('academic_pathway_id');
            }
        }

        if (Schema::hasColumn($curriculumTable, 'degree_track_id')) {
            $degreeTrackId = (int) ($student->degree_track_id ?? 0);
            if ($degreeTrackId > 0) {
                $courseIdsQuery->where('degree_track_id', $degreeTrackId);
            } else {
                $courseIdsQuery->whereNull('degree_track_id');
            }
        }

        $courseIds = $courseIdsQuery
            ->pluck('course_id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        if ($courseIds->isEmpty()) {
            return [];
        }

        $courses = ProgramCourseMaster::query()
            ->with(['coursetypemaster:id,title'])
            ->whereIn('id', $courseIds->all())
            ->orderBy('course_title')
            ->get()
            ->filter(fn($course) => $this->isMdcCourse($course))
            ->map(function ($course) {
                return [
                    'id' => (int) ($course->id ?? 0),
                    'label' => trim(((string) ($course->course_code ?? '')) . ' - ' . ((string) ($course->course_title ?? ''))),
                ];
            })
            ->filter(fn($course) => !empty($course['id']))
            ->values()
            ->all();

        return $courses;
    }

    private function resolveCurrentMdcEnrollmentForStudent(StudentMaster $student, int $semesterId): ?array
    {
        if ($semesterId <= 0 || empty($student->id)) {
            return null;
        }

        $academicYear = (string) (optional($student->batchmaster)->batch_name ?: date('Y'));

        $currentElective = StudentCourseInfo::query()
            ->with(['coursemaster.coursetypemaster:id,title'])
            ->where('student_id', (int) $student->id)
            ->where('semester', $semesterId)
            ->where('is_elective', 1)
            ->where(function ($query) use ($academicYear) {
                $query->where('academic_year', $academicYear)
                    ->orWhereNull('academic_year')
                    ->orWhere('academic_year', '');
            })
            ->orderByDesc('id')
            ->first();

        if ($currentElective) {
            return [
                'course_id' => (int) ($currentElective->course_id ?? 0),
                'label' => trim(((string) ($currentElective->coursemaster->course_code ?? '')) . ' - ' . ((string) ($currentElective->coursemaster->course_title ?? ''))),
            ];
        }

        return null;
    }

    private function isMdcCourse($course): bool
    {
        if (!$course) {
            return false;
        }

        $typeTitle = strtoupper(trim((string) ($course->coursetypemaster->title ?? '')));
        if ($typeTitle !== '' && preg_replace('/\s.*/', '', $typeTitle) === ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE) {
            return true;
        }

        $courseCode = strtoupper(trim((string) ($course->course_code ?? '')));
        return str_contains($courseCode, ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE);
    }

    public function lateralEntryIndex()
    {
        $programstype = ProgramMaster::all();
        $batches = BatchMaster::orderByDesc('id')->get();
        $campuses = \App\Models\Campus::orderBy('name')->get();
        $departments = Subject::all();
        $programs = StudentProgram::orderBy('name')->get();
        $semesters = Semester::all();
        $bloodGroups = BloodGroupMaster::orderBy('name')->get();
        $religions = ReligionMaster::orderBy('name')->get();
        $nationalities = NationalityMaster::orderBy('name')->get();
        $auditLogs = LateralEntryAuditLog::with(['student', 'user'])->latest('id')->take(20)->get();

        return view('admin.itcell.lateral-entry', compact('batches', 'campuses', 'departments', 'programs', 'auditLogs', 'semesters', 'programstype', 'bloodGroups', 'religions', 'nationalities'));
    }

    public function storeLateralEntry(Request $request)
    {

        $validated = $request->validate([
            'application_code' => 'nullable|string|max:30',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'gender' => 'required|in:1,2',
            'mobile_no' => 'nullable|string|max:15',
            'mail_id' => 'nullable|email|max:150',
            'campus_id' => 'required|integer|exists:campuses,id',
            'department' => 'nullable|integer|exists:subjects,id',
            'new_program_id' => 'required|integer|exists:student_program,id',
            'batch' => 'required|integer|exists:batch_masters,id',
            'admission_date' => 'nullable|date',
            'current_year' => 'nullable|integer|min:1|max:6',
            'remarks' => 'nullable|string|max:500',
            'semester' => 'required|integer|exists:semesters,id',
            'dob' => 'nullable|date',
            'blood_group_id' => 'nullable|integer|exists:blood_group_masters,id',
            'religion' => 'nullable|integer|exists:religion_masters,id',
            'nationality' => 'nullable|integer|exists:nationality_masters,id',
            'mother_tongue' => 'nullable|string|max:120',
            'caste' => 'nullable|string|max:120',
            'aadhar_no' => 'nullable|string|max:30',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'guardian_name' => 'nullable|string|max:255',
            'fr_mobile_no' => 'nullable|string|max:20',
            'mr_mobile_no' => 'nullable|string|max:20',
            'guardian_mobile_no' => 'nullable|string|max:20',
            'fr_occupation' => 'nullable|string|max:255',
            'mr_occupation' => 'nullable|string|max:255',
            'annual_income' => 'nullable|numeric|min:0|max:999999999',
            'address' => 'nullable|string|max:1200',
            'city' => 'nullable|string|max:150',
            'district' => 'nullable|string|max:150',
            'state' => 'nullable|string|max:150',
            'pincode' => 'nullable|string|max:20',
            'x_percentage' => 'nullable|numeric|min:0|max:100',
            'xii_percentage' => 'nullable|numeric|min:0|max:100',
            'ug_percentage' => 'nullable|numeric|min:0|max:100',
            'sgpa1' => 'nullable|numeric|min:0|max:10',
            'sgpa2' => 'nullable|numeric|min:0|max:10',
            'sgpa3' => 'nullable|numeric|min:0|max:10',
            'sgpa4' => 'nullable|numeric|min:0|max:10',
            'sgpa5' => 'nullable|numeric|min:0|max:10',
            'sgpa6' => 'nullable|numeric|min:0|max:10',
            'application_form_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $batch = BatchMaster::findOrFail($request->batch);
            $program = StudentProgram::findOrFail($request->new_program_id);
            $campusId = (int) $request->campus_id;
            $departmentId = (int) ($validated['department'] ?? 0);

            $rollNo = $this->generateLateralEntryRollNo($request->program_type, $batch->batch_name, $program->code, $campusId, $departmentId);

            $applicationFormPath = null;
            if ($request->hasFile('application_form_file')) {
                $applicationFormPath = $request->file('application_form_file')->store('lateral-entry/application-forms', 'public');
            }

            $fullAddress = $this->buildAddressString(
                $validated['address'] ?? null,
                $validated['city'] ?? null,
                $validated['district'] ?? null,
                $validated['state'] ?? null,
                $validated['pincode'] ?? null,
            );

            $snapshot = [
                'personal' => [
                    'dob' => $validated['dob'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'blood_group_id' => $validated['blood_group_id'] ?? null,
                    'religion' => $validated['religion'] ?? null,
                    'nationality' => $validated['nationality'] ?? null,
                    'mother_tongue' => $validated['mother_tongue'] ?? null,
                    'caste' => $validated['caste'] ?? null,
                    'aadhar_no' => $validated['aadhar_no'] ?? null,
                ],
                'address' => [
                    'line' => $validated['address'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'district' => $validated['district'] ?? null,
                    'state' => $validated['state'] ?? null,
                    'pincode' => $validated['pincode'] ?? null,
                ],
                'academic' => [
                    'x_percentage' => $validated['x_percentage'] ?? null,
                    'xii_percentage' => $validated['xii_percentage'] ?? null,
                    'ug_percentage' => $validated['ug_percentage'] ?? null,
                    'sgpa1' => $validated['sgpa1'] ?? null,
                    'sgpa2' => $validated['sgpa2'] ?? null,
                    'sgpa3' => $validated['sgpa3'] ?? null,
                    'sgpa4' => $validated['sgpa4'] ?? null,
                    'sgpa5' => $validated['sgpa5'] ?? null,
                    'sgpa6' => $validated['sgpa6'] ?? null,
                ],
            ];

            $student = StudentMaster::create([
                'user_code' => $validated['application_code'] ?? null,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'gender' => $validated['gender'],
                'dob' => $validated['dob'] ?? null,
                'mobile_no' => $validated['mobile_no'] ?? null,
                'mail_id' => $validated['mail_id'] ?? null,
                'campus_id' => $campusId,
                'department' => $validated['department'] ?? null,
                'academic_dept_id' => $validated['department'] ?? null,
                'new_program_id' => $request->new_program_id,
                'batch' => $batch->id,
                'admission_date' => $validated['admission_date'] ?? now()->toDateString(),
                'current_year' => $validated['current_year'] ?? 1,
                'graduation_year' => (int) $batch->batch_name + 4,
                'roll_no' => $rollNo,
                'blood_group_id' => $validated['blood_group_id'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'nationality' => $validated['nationality'] ?? null,
                'mother_tongue' => $validated['mother_tongue'] ?? null,
                'caste' => $validated['caste'] ?? null,
                'aadhar_no' => $validated['aadhar_no'] ?? null,
                'father_name' => $validated['father_name'] ?? null,
                'mother_name' => $validated['mother_name'] ?? null,
                'guardian_name' => $validated['guardian_name'] ?? null,
                'fr_mobile_no' => $validated['fr_mobile_no'] ?? null,
                'mr_mobile_no' => $validated['mr_mobile_no'] ?? null,
                'guardian_mobile_no' => $validated['guardian_mobile_no'] ?? null,
                'fr_occupation' => $validated['fr_occupation'] ?? null,
                'mr_occupation' => $validated['mr_occupation'] ?? null,
                'annual_income' => $validated['annual_income'] ?? null,
                'address' => $fullAddress,
                'hsc_percentage' => $validated['xii_percentage'] ?? null,
                'photo_path' => $applicationFormPath,
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'active',
                'user_type' => 'student',
            ]);

            // Mark selected semester as active for the newly created student.
            StudentSemesterConfig::where('student_id', $student->id)->update([
                'current_semester' => 0,
            ]);

            StudentSemesterConfig::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'semester_id' => (string) $validated['semester'],
                ],
                [
                    'current_semester' => 1,
                ]
            );

            $auditPayload = [
                'student_id' => $student->id,
                'user_id' => Auth::id(),
                'entry_type' => 'lateral-entry',
                'remarks' => $validated['remarks'] ?? 'Lateral entry student created',
                'source' => 'itcell',
                'created_at' => now(),
            ];

            // Keep insert compatible when optional migration columns are not yet present.
            if (Schema::hasColumn('lateral_entry_audit_logs', 'application_form_path')) {
                $auditPayload['application_form_path'] = $applicationFormPath;
            }
            if (Schema::hasColumn('lateral_entry_audit_logs', 'sourced_application_code')) {
                $auditPayload['sourced_application_code'] = $validated['application_code'] ?? null;
            }
            if (Schema::hasColumn('lateral_entry_audit_logs', 'application_snapshot')) {
                $auditPayload['application_snapshot'] = $snapshot;
            }

            LateralEntryAuditLog::create($auditPayload);
        });

        return redirect()->route('itcell.lateral-entry.index')->with('success', 'Lateral entry student created successfully.');
    }

    public function getProgramsForLateralEntry(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|integer|exists:batch_masters,id',
            'campus_id' => 'required|integer|exists:campuses,id',
        ]);

        $programIds = StudentMaster::query()
            ->where('batch', (int) $validated['batch_id'])
            ->where('campus_id', (int) $validated['campus_id'])
            ->whereNotNull('new_program_id')
            ->where('new_program_id', '!=', '')
            ->pluck('new_program_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $programs = StudentProgram::query()
            ->whereIn('id', $programIds)
            ->where('campus_id', (int) $validated['campus_id'])
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'campus_id']);

        return response()->json([
            'success' => true,
            'programs' => $programs->map(function ($program) {
                return [
                    'id' => $program->id,
                    'name' => $program->name,
                    'code' => $program->code,
                    'campus_id' => $program->campus_id,
                ];
            }),
        ]);
    }

    public function getLateralEntryApplicationData(Request $request)
    {
        $validated = $request->validate([
            'application_code' => 'required|string|max:30',
        ]);

        $application = AdmissionApplication::with('registrationmaster.countrymaster')
            ->where('application_code', $validated['application_code'])
            ->first();

        if (!$application || !$application->registrationmaster) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found for the provided code.',
            ], 404);
        }

        $registration = $application->registrationmaster;
        $countryName = (string) ($registration->countrymaster->name ?? '');
        $nationalityId = $this->resolveNationalityIdFromCountry($countryName);
        $program = null;
        if (!empty($application->course)) {
            $program = StudentProgram::find((int) $application->course);
        }

        return response()->json([
            'success' => true,
            'application' => [
                'application_code' => (string) $application->application_code,
                'first_name' => (string) ($registration->first_name ?? ''),
                'last_name' => (string) ($registration->last_name ?? ''),
                'gender' => $this->mapAdmissionGenderToStudentGender($application->gender ?? null),
                'mobile_no' => (string) ($registration->mobile_no ?? ''),
                'mail_id' => (string) ($registration->mail_id ?? ''),
                'campus_id' => (int) ($registration->campus_id ?? 0),
                'batch' => (int) ($registration->batch ?? 0),
                'department' => is_numeric($application->department) ? (int) $application->department : null,
                'new_program_id' => is_numeric($application->course) ? (int) $application->course : null,
                'program_type' => $program ? (int) $program->program_type : null,
                'dob' => $application->dob,
                'blood_group_id' => is_numeric($application->bloodgroup) ? (int) $application->bloodgroup : null,
                'religion' => is_numeric($application->religion) ? (int) $application->religion : null,
                'nationality' => $nationalityId,
                'mother_tongue' => (string) ($application->mothertongue ?? ''),
                'caste' => (string) ($application->caste ?? ''),
                'aadhar_no' => (string) ($application->adhaar ?? ''),
                'father_name' => (string) ($application->father_name ?? ''),
                'mother_name' => (string) ($application->mother_name ?? ''),
                'guardian_name' => (string) ($application->guardian_name ?? ''),
                'fr_mobile_no' => (string) ($application->father_contact ?? ''),
                'mr_mobile_no' => (string) ($application->mother_contact ?? ''),
                'guardian_mobile_no' => (string) ($application->guardian_contact ?? ''),
                'fr_occupation' => (string) ($application->father_occupation ?? ''),
                'mr_occupation' => (string) ($application->mother_occupation ?? ''),
                'annual_income' => $application->income,
                'address' => (string) ($application->permanent_address ?? ''),
                'city' => (string) ($application->city ?? ''),
                'district' => (string) ($application->district ?? ''),
                'state' => (string) ($application->state ?? ''),
                'pincode' => (string) ($application->pincode ?? ''),
                'x_percentage' => $this->computePercentage([
                    $application->score10_1,
                    $application->score10_2,
                    $application->score10_3,
                    $application->score10_4,
                    $application->score10_5,
                ]),
                'xii_percentage' => $this->computePercentage([
                    $application->score12_1,
                    $application->score12_2,
                    $application->score12_3,
                    $application->score12_4,
                ]),
                'ug_percentage' => null,
                'sgpa1' => $application->sgpa1,
                'sgpa2' => $application->sgpa2,
                'sgpa3' => $application->sgpa3,
                'sgpa4' => $application->sgpa4,
                'sgpa5' => $application->sgpa5,
                'sgpa6' => $application->sgpa6,
            ],
        ]);
    }

    public function lateralEntryAudit()
    {
        $logs = LateralEntryAuditLog::with(['student', 'user'])->latest('id')->paginate(20);

        return view('admin.itcell.lateral-entry-audit', compact('logs'));
    }

    public function studentCampusTransferIndex(Request $request)
    {
        $allowedCampusIds = [1, 2];

        $campuses = Campus::query()
            ->whereIn('id', $allowedCampusIds)
            ->orderBy('id')
            ->get(['id', 'name', 'slug']);

        $selectedCampusId = (int) $request->input('campus_id', 1);
        if (!in_array($selectedCampusId, $allowedCampusIds, true)) {
            $selectedCampusId = 1;
        }

        $selectedProgramId = (int) $request->input('program_id', 0);
        if ($selectedProgramId < 0) {
            $selectedProgramId = 0;
        }

        $search = trim((string) $request->input('search', ''));

        $batchIdsWithStudents = StudentMaster::query()
            ->whereIn('campus_id', $allowedCampusIds)
            ->whereNotNull('batch')
            ->distinct()
            ->pluck('batch')
            ->filter(fn($id) => !empty($id))
            ->map(fn($id) => (int) $id)
            ->values();

        $batches = BatchMaster::query()
            ->whereIn('id', $batchIdsWithStudents)
            ->orderByDesc('batch_name')
            ->get(['id', 'batch_name']);

        $enrolledProgramOptionsQuery = StudentProgram::query()
            ->select('student_program.id', 'student_program.name', 'student_program.code', 'student_program.campus_id')
            ->join('student_masters', 'student_masters.new_program_id', '=', 'student_program.id')
            ->whereIn('student_program.campus_id', $allowedCampusIds)
            ->where('student_masters.campus_id', $selectedCampusId)
            ->whereNotNull('student_masters.new_program_id');

        $enrolledPrograms = $enrolledProgramOptionsQuery
            ->distinct()
            ->orderBy('student_program.name')
            ->get();

        if ($selectedProgramId > 0 && !$enrolledPrograms->contains('id', $selectedProgramId)) {
            $selectedProgramId = 0;
        }

        $studentsQuery = StudentMaster::query()
            ->with([
                'campusmaster:id,name',
                'stdprogramenrolled:id,name,code,campus_id',
                'batchmaster:id,batch_name',
            ])
            ->whereIn('campus_id', $allowedCampusIds)
            ->where('campus_id', $selectedCampusId);

        if ($selectedProgramId > 0) {
            $studentsQuery->where('new_program_id', $selectedProgramId);
        }

        if ($search !== '') {
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('roll_no', 'like', '%' . $search . '%')
                    ->orWhere('register_no', 'like', '%' . $search . '%')
                    ->orWhere('user_code', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%');
            });
        }

        $students = $studentsQuery
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(30)
            ->appends([
                'campus_id' => $selectedCampusId,
                'program_id' => $selectedProgramId,
                'search' => $search,
            ]);

        $recentTransfers = StudentCampusTransferLog::query()
            ->with([
                'student:id,first_name,last_name,roll_no',
                'fromCampus:id,name',
                'toCampus:id,name',
                'fromProgram:id,name,code',
                'toProgram:id,name,code',
                'changedByUser:id,name',
            ])
            ->latest('id')
            ->limit(20)
            ->get();

        return view('admin.itcell.student-campus-transfer', [
            'campuses' => $campuses,
            'batches' => $batches,
            'selectedCampusId' => $selectedCampusId,
            'selectedProgramId' => $selectedProgramId,
            'search' => $search,
            'students' => $students,
            'allowedCampusIds' => $allowedCampusIds,
            'enrolledPrograms' => $enrolledPrograms,
            'recentTransfers' => $recentTransfers,
        ]);
    }

    public function storeStudentCampusTransfer(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:student_masters,id',
            'to_campus_id' => 'required|integer|in:1,2',
            'to_batch_id' => 'required|integer|exists:batch_masters,id',
            'to_program_id' => 'required|integer|exists:student_program,id',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $student = StudentMaster::query()
                    ->lockForUpdate()
                    ->findOrFail((int) $validated['student_id']);

                if (!in_array((int) $student->campus_id, [1, 2], true)) {
                    throw ValidationException::withMessages([
                        'student_id' => 'Only Sonada/Siliguri students can be transferred with this module.',
                    ]);
                }

                $toCampusId = (int) $validated['to_campus_id'];
                if ((int) $student->campus_id === $toCampusId) {
                    throw ValidationException::withMessages([
                        'to_campus_id' => 'Target campus must be different from current campus.',
                    ]);
                }

                $toProgram = StudentProgram::query()->findOrFail((int) $validated['to_program_id']);
                $toBatchId = (int) $validated['to_batch_id'];
                if ((int) $toProgram->campus_id !== $toCampusId) {
                    throw ValidationException::withMessages([
                        'to_program_id' => 'Selected enrolled program does not belong to the target campus.',
                    ]);
                }

                $targetEnrollmentExists = StudentMaster::query()
                    ->where('campus_id', $toCampusId)
                    ->where('batch', $toBatchId)
                    ->where('new_program_id', (int) $toProgram->id)
                    ->exists();

                if (!$targetEnrollmentExists) {
                    throw ValidationException::withMessages([
                        'to_program_id' => 'Selected enrolled program is not available for the chosen target campus and batch.',
                    ]);
                }

                $oldSnapshot = [
                    'student_id' => $student->id,
                    'roll_no' => $student->roll_no,
                    'campus_id' => $student->campus_id,
                    'batch' => $student->batch,
                    'new_program_id' => $student->new_program_id,
                    'department' => $student->department,
                    'academic_dept_id' => $student->academic_dept_id,
                    'status' => $student->status,
                ];

                $oldProgramId = $student->new_program_id;
                $oldCampusId = $student->campus_id;
                $oldDepartmentId = $student->department;
                $newDepartmentId = $toProgram->department ?: $student->department;

                // Keep roll_no unchanged; transfer only campus/program and linked department fields.
                $student->update([
                    'campus_id' => $toCampusId,
                    'batch' => $toBatchId,
                    'new_program_id' => $toProgram->id,
                    'department' => $newDepartmentId,
                    'academic_dept_id' => $newDepartmentId,
                ]);

                $newSnapshot = [
                    'student_id' => $student->id,
                    'roll_no' => $student->roll_no,
                    'campus_id' => $student->campus_id,
                    'batch' => $student->batch,
                    'new_program_id' => $student->new_program_id,
                    'department' => $student->department,
                    'academic_dept_id' => $student->academic_dept_id,
                    'status' => $student->status,
                ];

                StudentCampusTransferLog::create([
                    'student_id' => $student->id,
                    'roll_no' => $student->roll_no,
                    'from_campus_id' => (int) $oldCampusId,
                    'to_campus_id' => $toCampusId,
                    'from_program_id' => $oldProgramId,
                    'to_program_id' => $toProgram->id,
                    'from_department_id' => $oldDepartmentId,
                    'to_department_id' => $newDepartmentId,
                    'changed_by' => Auth::id(),
                    'reason' => $validated['reason'] ?? null,
                    'old_snapshot' => $oldSnapshot,
                    'new_snapshot' => $newSnapshot,
                    'created_at' => now(),
                ]);
            });

            return back()->with('success', 'Student transferred successfully. Roll number remains unchanged and transfer history is recorded.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()->with('error', 'Transfer failed. Please try again.');
        }
    }

    public function getStudentCampusTransferPrograms(Request $request)
    {
        $allowedCampusIds = [1, 2];

        $validated = $request->validate([
            'campus_id' => 'required|integer|in:1,2',
            'batch_id' => 'nullable|integer|min:1',
        ]);

        $campusId = (int) $validated['campus_id'];
        if (!in_array($campusId, $allowedCampusIds, true)) {
            return response()->json([
                'success' => true,
                'programs' => [],
            ]);
        }

        $programsQuery = StudentProgram::query()
            ->select('student_program.id', 'student_program.code', 'student_program.name')
            ->join('student_masters', 'student_masters.new_program_id', '=', 'student_program.id')
            ->where('student_program.campus_id', $campusId)
            ->where('student_masters.campus_id', $campusId)
            ->whereNotNull('student_masters.new_program_id');

        if (!empty($validated['batch_id'])) {
            $programsQuery->where('student_masters.batch', (int) $validated['batch_id']);
        }

        $programs = $programsQuery
            ->distinct()
            ->orderBy('student_program.name')
            ->get()
            ->map(function ($program) {
                return [
                    'id' => (int) $program->id,
                    'code' => (string) ($program->code ?? ''),
                    'name' => (string) ($program->name ?? ''),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'programs' => $programs,
        ]);
    }

    private function generateLateralEntryRollNo(int $program_type, $batchName, ?string $programCode, int $campusId, int $departmentId): string
    {
        $prefix = $campusId === 1 ? 'SO' : 'SL';
        $programCode = strtoupper((string) $programCode);
        $batch = (string) $batchName;
        $prog = $program_type == 1 ? 'U' : 'P';
        $base = $prog . $prefix . $batch . $programCode;

        $existingRollNos = StudentMaster::query()
            ->where('roll_no', 'like', $base . '%')
            ->pluck('roll_no');

        $usedNumbers = [];
        foreach ($existingRollNos as $rollNo) {
            if (str_starts_with((string) $rollNo, $base) && strlen((string) $rollNo) > strlen($base)) {
                $suffix = substr((string) $rollNo, strlen($base));
                if (ctype_digit($suffix)) {
                    $usedNumbers[(int) $suffix] = true;
                }
            }
        }

        $nextNumber = 1;
        while (isset($usedNumbers[$nextNumber])) {
            $nextNumber++;
        }

        return $base . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    private function mapAdmissionGenderToStudentGender(?string $gender): ?string
    {
        $value = strtolower(trim((string) $gender));
        if ($value === 'male') {
            return '1';
        }
        if ($value === 'female') {
            return '2';
        }
        return null;
    }

    private function resolveNationalityIdFromCountry(string $countryName): ?int
    {
        $countryName = trim($countryName);
        if ($countryName === '') {
            return null;
        }

        return NationalityMaster::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($countryName)])
            ->value('id');
    }

    private function buildAddressString(?string $line, ?string $city, ?string $district, ?string $state, ?string $pincode): ?string
    {
        $parts = array_filter([
            trim((string) $line),
            trim((string) $city),
            trim((string) $district),
            trim((string) $state),
            trim((string) $pincode),
        ], fn($value) => $value !== '');

        if (empty($parts)) {
            return null;
        }

        return implode(', ', $parts);
    }

    private function computePercentage(array $scores): ?float
    {
        $valid = collect($scores)
            ->filter(fn($value) => $value !== null && $value !== '' && is_numeric($value))
            ->map(fn($value) => (float) $value)
            ->values();

        if ($valid->isEmpty()) {
            return null;
        }

        return round($valid->avg(), 2);
    }

    function generateLibraryCode(Request $request)
    {
        $request->validate([
            'batch' => 'required',
            'campus' => 'required',
            'action_type' => 'required|in:generate,download'
        ]);

        $batch = $request->batch;
        $campus_id = $request->campus;
        $students = StudentMaster::where('batch', $batch)
            ->where('campus_id', $campus_id)
            ->with('batchmaster:id,batch_name')
            ->with('stdprogramenrolled')
            ->orderBy('id')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No students found for the selected batch and campus.');
        }

        if ($request->action_type == 'generate') {
            $lastSequence = 0;
            $usedCodes = [];

            $existingCodes = StudentMaster::whereNotNull('library_code')
                ->where('library_code', '!=', '')
                ->pluck('library_code');

            foreach ($existingCodes as $libraryCode) {
                if (preg_match('/(\d{4})$/', (string) $libraryCode, $matches)) {
                    $currentSequence = (int) $matches[1];
                    $usedCodes[$currentSequence] = true;
                    if ($currentSequence > $lastSequence) {
                        $lastSequence = $currentSequence;
                    }
                }
            }

            $updatedCount = 0;

            foreach ($students as $student) {
                if (!empty($student->library_code)) {
                    continue;
                }

                do {
                    $lastSequence++;
                } while (isset($usedCodes[$lastSequence]) && $lastSequence <= 9999);

                if ($lastSequence > 9999) {
                    return back()->with('error', 'Unable to generate unique 4-digit library codes. Sequence limit reached.');
                }

                $usedCodes[$lastSequence] = true;
                $student->library_code = str_pad((string) $lastSequence, 4, '0', STR_PAD_LEFT);
                $student->save();
                $updatedCount++;
            }

            return back()->with('success', $updatedCount . ' library code(s) generated successfully.');
        }


        if ($request->action_type == 'download') {
            $filename = 'student-library-code-list-batch-' . $batch . '-campus-' . $campus_id . '-' . date('Y-m-d') . '.xlsx';
            return Excel::download(new StudentLibraryCodeExport($students), $filename);
        }

        return back()->with('error', 'Invalid action type selected.');
    }

    function generateExcelStudentData(Request $request)
    {

        $batch = $request->batch;
        $campus_id = $request->campus;
        $with = [
            'religionmaster:id,name',
            'campusmaster:id,slug,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'deptmaster:id,name',
            'stdprogramenrolled',
            'academicpathway',
            'degreetrack',
            'singleselection',
            'activeSemesterConfig',
        ];

        if (Schema::hasTable('student_addresses')) {
            $with[] = 'address';
        }

        $students = StudentMaster::where('batch', $batch)
            ->where('campus_id', $campus_id)
            ->with($with)
            ->orderBy('id')
            ->get();

        $filename = 'student-list-batch-' . $batch . '-campus-' . $campus_id . '-' . date('Y-m-d') . '.xlsx';
        return Excel::download(new StudentDataExport($students), $filename);
    }
}
