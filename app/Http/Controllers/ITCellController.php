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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ITCellController extends Controller
{

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
