<?php

namespace App\Http\Controllers;

use App\Exports\StudentDataExport;
use App\Exports\StudentLibraryCodeExport;
use App\Models\AcademicPathwayMaster;
use App\Models\AdmissionApplication;
use App\Models\AnnualPromotionLog;
use App\Models\BatchMaster;
use App\Models\DegreeTrackMaster;
use App\Models\StudentSemesterConfig;
use App\Models\StudentMaster;
use App\Models\StudentProgram;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
