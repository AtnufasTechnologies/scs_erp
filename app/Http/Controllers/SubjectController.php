<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\AdmissionApplication;
use App\Models\AcademicPathwayMaster;
use App\Models\BatchMaster;
use App\Models\Campus;
use App\Models\CognitiveLevelMaster;
use App\Models\CoHasCso;
use App\Models\CourseObjective;
use App\Models\DegreeTrackMaster;
use App\Models\CsoSubunit;
use App\Models\Department;
use App\Models\DepartmentMaster;
use App\Models\DepartmentActivity;
use App\Models\Faculty;
use App\Models\LectureHallMaster;
use App\Models\MainProgram;
use App\Models\PoHasCo;
use App\Models\ProgramCourseMaster;
use App\Models\ProgramMaster;
use App\Models\Semester;
use App\Models\ShiftMaster;
use App\Models\StudentMaster;
use App\Models\StudentAttendance;
use App\Models\StudentProgram;
use App\Models\Subject;
use App\Models\SubjectCombinationMaster;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasCombination;
use App\Models\SubjectHasDeptAdmin;
use App\Models\CiaMark;
use App\Models\InterMark;
use App\Models\StudentCourseInfo;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSemester;
use App\Models\ExamSystem\ExamStudent;
use App\Models\ExamSystem\Registration;
use App\Models\ExamSystem\Result;
use App\Models\SubjectHasStudentProgam;
use App\Models\SubjectHasSyllabus;
use App\Models\SubjectTypeMaster;
use App\Models\TeachingAssignment;
use App\Models\TeachingAssignmentFaculty;
use App\Models\ReligionMaster;
use App\Models\NationalityMaster;
use App\Models\BloodGroupMaster;
use App\Models\SyllabusHasFaculty;
use App\Models\CourseSeatAllocation;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\SpecializationMaster;
use App\Models\StdProgComboMap;
use App\Models\SubunitHasRbt;
use App\Models\SyllabusPdfUpload;
use App\Models\SyllabusManager;
use App\Models\SyllabusSubunit;
use App\Models\User;
use App\Models\UserHasRole;
use App\Repositories\Dean\Student360Repository;
use App\Services\StudentTimetableService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class SubjectController extends Controller
{
    function index()
    {
        $data = Subject::with([
            'campusmaster'
        ])->latest()->get();

        $shiftMasters = ShiftMaster::where('is_active', 1)
            ->orderBy('sort_order')
            ->get(['id', 'title', 'slug']);

        return view('admin.master.subject', [
            'data' => $data,
            'shiftMasters' => $shiftMasters,
        ]);
    }

    function subjectType()
    {
        $data = SubjectTypeMaster::get();
        return view('admin.master.subject-type', ['data' => $data]);
    }

    function addSubject(Request $request)
    {

        $request->validate([
            'code' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'campus' => 'required',
        ]);
        $slug = Str::slug($request->title);
        $code = Str::upper($request->code);
        $check = Subject::where('code', $code)->where('campus_id', $request->campus)->count();
        if ($check > 0) {
            return response()->json(['msg' => 'Subject already exists', 'status' => 'error']);
        } else {
            $mainStream = ProgramMaster::find($request->program_id);
            if ($request->campus == 3) {
                $campuses = Campus::all();
                foreach ($campuses as $campus) {
                    $rec = new Subject();
                    $rec->campus_id = $campus->id;
                    $rec->main_program_type = $mainStream->title;
                    $rec->slug =   $slug;
                    $rec->code = $request->code;
                    $rec->title = $request->title;
                    $rec->save();
                }
            } else {
                $rec = new Subject();
                $rec->campus_id = $request->campus;
                $rec->main_program_type = $mainStream->title;
                $rec->slug =   $slug;
                $rec->code = $request->code;
                $rec->title = $request->title;
                $rec->save();
            }


            return redirect()->back()->with('success', 'Created');
        }
    }

    function subjectSingle(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $subjectId = $request->id;
        $subject = Subject::with(['semesters'])->find($subjectId);

        // Check if subject exists
        if (!$subject) {
            return redirect()->back()->with('error', 'Subject not found');
        }

        // Course Master
        $courseMaster = $subject;

        // Number of Students (total students in all batches for this subject/department)
        $studentsCount = 0;
        $batchWiseStudents = [];
        $semestersCount = $subject->semesters?->count() ?? 0;

        // Get all batches
        $batches = BatchMaster::all();
        foreach ($batches as $batch) {
            $studentCount = \App\Models\StudentMaster::where('department', $subjectId)
                ->where('batch', $batch->id)
                ->when(Schema::hasColumn('student_masters', 'is_left'), fn($query) => $query->where('is_left', 0))
                ->count();
            $batchWiseStudents[] = [
                'batch_name' => $batch->batch_name,
                'student_count' => $studentCount
            ];
            $studentsCount += $studentCount;
        }


        // For combinations modal
        if (!empty($request->batch)) {
            $activeBatch = $request->batch;
        } else {
            $activeBatch = BatchMaster::where('admission_active_batch', 1)->value('id');
        }

        $combinations = SubjectHasStudentProgam::where('subject_id', $subjectId)
            ->with(['studentprograminfo', 'batchmaster', 'shiftmaster'])
            ->where('batch_id', $activeBatch)
            ->get();


        $programs = StudentProgram::where('campus_id', $subject->campus_id)->get();
        $course_master_count = SubjectCourseMaster::where('subject_id', $subjectId)->count();
        $faculties = SubjectFacultyMaster::with('faculty')->where('subject_id', $subjectId)->get();
        return view('admin.subject.department-dashboard', [
            'data' => $courseMaster,
            'students_count' => $studentsCount,
            'semesters_count' => $semestersCount,
            'batchWiseStudents' => $batchWiseStudents,
            'combinations' => $combinations,
            'programs' => $programs,
            'course_master_count' => $course_master_count,
            'deptfaculties' => $faculties,
        ]);
    }


    function viewRoutine($id)
    {
        $data =  SubjectHasSyllabus::where('subject_id', $id)->with([
            'sessionmaster:id,title',
            'semestermaster:id,title',
            'subtypemaster:id,title',
            'timetable.weekdaymaster:id,title',
            'timetable.hourmaster:id,title',
            'timetable.lecturehallmaster.acblockmaster:id,title',
        ])->get();
        return response()->json(['data' => $data]);
    }

    function addSyllabus(Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'batch' => 'required',
            'semester_id' => 'required',
            'title' => 'required',
            'desc' => 'required',
            'subject_type_id' => 'required'
        ]);



        $rec = new SubjectHasSyllabus();
        $rec->dept_id = $request->dept_id;
        $rec->subject_id = $request->subject_id;
        $rec->session_id = $request->session_id;
        $rec->semester_id = $request->semester_id;
        $rec->title = $request->title;
        $rec->content = $request->desc;
        $rec->subject_type_id = $request->subject_type_id;
        $rec->save();
        return response()->json(['msg' => 'Data Added', 'status' => 'success']);
    }

    function cogLevelMaster()
    {
        $data = CognitiveLevelMaster::latest()->get();
        return response()->json(['data' => $data]);
    }
    function addCognitiveLevel(Request $request)
    {
        $validator  =  Validator::make($request->all(), [
            'shortname' => 'required',
            'fullname' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'status' => false], 400);
        }

        $rec = new CognitiveLevelMaster();
        $rec->shortname = $request->shortname;
        $rec->fullname = $request->fullname;
        $rec->save();
        return response()->json(['message' => 'Cognitive Level Created'], 201);
    }


    function addSubjectTimeTable(Request $request)
    {
        $allowedShifts = $this->getShiftSlugs();
        $subjectId = SubjectHasSyllabus::where('id', $request->syllabus_id)->value('subject_id');
        $usesShifts = $this->subjectUsesShifts($subjectId);
        $validator  =  Validator::make($request->all(), [
            'syllabus_id' => 'required',
            'weekday_id' => 'required',
            'hour_id' => 'required',
            'lecturehall_id' => 'required',
            'shift' => ['nullable', Rule::in($allowedShifts)],

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'status' => false], 400);
        }

        $rec = new SubjectHasRoutine();
        $rec->syllabus_id = $request->syllabus_id;
        $rec->weekday_id = $request->weekday_id;
        $rec->hour_id = $request->hour_id;
        $rec->lecturehall_id = $request->lecturehall_id;
        $rec->shift = $usesShifts ? ($request->shift ?? $this->getDefaultShiftSlug()) : $this->getDefaultShiftSlug();
        $rec->save();
        return response()->json(['message' => 'TimeTable Created'], 201);
    }

    function addSemesterToSubject(Request $request)
    {

        $request->validate([
            'semesters' => 'required|array|min:1',
            'subject_id' => 'required',
            'batch' => 'required',
        ]);


        $semesters = $request->semesters;
        $subject_id = $request->subject_id;
        $batch = $request->batch;


        for ($i = 0; $i < count($semesters); $i++) {

            $check = SubjectHasSemester::where('subject_id', $subject_id)
                ->where('semester_id', $semesters[$i])
                ->where('batch_id', $batch)
                ->doesntExist();

            if ($check) {
                SubjectHasSemester::create([
                    'subject_id' => $subject_id,
                    'semester_id' => $semesters[$i],
                    'batch_id' => $batch,
                ]);
            }
        }
        return redirect()->back()->with('success', 'Semester(s) Added');
    }

    function deleteSubject($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();
        return redirect()->back()->with('success', 'Subject Deleted');
    }

    function linkStdPrograms(Request $request)
    {
        $validator  =  $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'batch_id' => 'required|integer|exists:batch_masters,id',
            'campus_id' => 'nullable|integer|exists:campuses,id',
            'programs' => 'required|array|min:1',
            'program_type' => 'required',
            'total_seats' => 'required|integer|min:0',
        ]);

        $userId  = Auth::user()->id;
        if (!UserHasRole::where('user_id', $userId)->whereIn('role_name', ['hod', 'dept-admin-erp', 'itcell'])->exists()) {
            return redirect()->back()->with('info', 'Unauthorized to Access this Tool');
        }

        $programs = $request->programs;
        $subject_id = $request->subject_id;
        $data = Subject::find($subject_id);

        if (!$data) {
            return redirect()->back()->with('info', 'Subject not found');
        }

        $selectedCampusId = (int) ($request->campus_id ?: $data->campus_id);
        if ($selectedCampusId <= 0) {
            return redirect()->back()->with('info', 'Invalid campus selected');
        }

        $allowedProgramIds = StudentMaster::where('batch', $request->batch_id)
            ->where('campus_id', $selectedCampusId)
            ->whereNotNull('new_program_id')
            ->distinct()
            ->pluck('new_program_id')
            ->map(fn($id) => (int) $id)
            ->all();

        if (!empty($allowedProgramIds)) {
            $allowedProgramIds = StudentProgram::where('campus_id', $selectedCampusId)
                ->whereIn('id', $allowedProgramIds)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->all();
        }

        $requestedProgramIds = collect($programs)->map(fn($id) => (int) $id)->all();
        $invalidProgramIds = array_values(array_diff($requestedProgramIds, $allowedProgramIds));

        if (!empty($invalidProgramIds)) {
            return redirect()->back()->with('info', 'One or more selected programs are not enrolled in the selected batch/campus. Please refresh and try again.');
        }
        for ($i = 0; $i < count($programs); $i++) {

            $recordCheck = SubjectHasStudentProgam::where('subject_id', $subject_id)
                ->where('batch_id', $request->batch_id)
                ->where('student_program_id', $programs[$i])
                ->where('campus_id', $selectedCampusId)
                ->where('program_type', $request->program_type)
                ->first();



            if ($recordCheck == null) {

                $departmentId =  StudentProgram::where('id', $programs[$i])->value('department');
                $subject = new SubjectHasStudentProgam();
                $subject->subject_id = $subject_id;
                $subject->batch_id = $request->batch_id;
                $subject->student_program_id = $programs[$i];
                $subject->campus_id = $selectedCampusId;
                $subject->program_type = $request->program_type;
                $subject->total_seats = $request->total_seats;
                $subject->total_available_seats = $request->total_seats;
                $subject->save();
            } else {
                return redirect()->back()->with('success', 'Combinations Already Linked');
            }
        }

        return redirect()->back()->with('success', 'Combinations linked successfully');
    }

    //department dashboard
    function departmentDashboard(Request $request)
    {
        $userId = Auth::user()->id;


        $subjectId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');


        $subject = Subject::with(['semesters', 'courseMasterPivot'])->find($subjectId);

        // Check if subject exists
        if (!$subject) {
            return redirect()->back()->with('info', 'Subject not found or user not assigned to any department');
        }

        $request->validate([
            'attendance_from' => 'nullable|date',
            'attendance_to' => 'nullable|date',
            'attendance_batch' => 'nullable|integer|exists:batch_masters,id',
            'attendance_course_id' => 'nullable|integer|exists:program_course_masters,id',
        ]);

        // Course Master
        $courseMaster = $subject;

        // Number of Students (total students in all batches for this subject/department)
        $studentsCount = 0;
        $batchWiseStudents = [];
        $semestersCount = $subject->semesters?->count() ?? 0;

        // Get all batches
        $batches = BatchMaster::all();
        foreach ($batches as $batch) {
            $studentCount = \App\Models\StudentMaster::where('department', $subjectId)
                ->where('batch', $batch->id)
                ->count();
            $batchWiseStudents[] = [
                'batch_name' => $batch->batch_name,
                'student_count' => $studentCount
            ];
            $studentsCount += $studentCount;
        }


        // For combinations modal
        if (!empty($request->batch)) {
            $activeBatch = (int) $request->batch;
        } else {
            $activeBatch = (int) BatchMaster::where('admission_active_batch', 1)->value('id');
        }

        if ($activeBatch <= 0) {
            $activeBatch = (int) BatchMaster::orderBy('id')->value('id');
        }

        $combinations = SubjectHasStudentProgam::where('subject_id', $subjectId)
            ->with(['studentprograminfo', 'batchmaster', 'shiftmaster'])
            ->where('batch_id', $activeBatch)
            ->withCount(['studentmaster' => function ($query) use ($activeBatch) {
                $query->where('batch', $activeBatch)
                    ->when(Schema::hasColumn('student_masters', 'is_left'), fn($inner) => $inner->where('is_left', 0));
            }])
            ->get();

        if (Schema::hasTable('integrated_program_sublayer_settings') && Schema::hasColumn('student_masters', 'integrated_origin_program_id')) {
            $integratedProgramIds = DB::table('integrated_program_sublayer_settings')
                ->where('is_active', 1)
                ->pluck('student_program_id')
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $id > 0)
                ->unique()
                ->values();

            if ($integratedProgramIds->isNotEmpty()) {
                $integratedStudentRows = StudentMaster::query()
                    ->where('batch', $activeBatch)
                    ->where(function ($query) use ($integratedProgramIds) {
                        $query->whereIn('new_program_id', $integratedProgramIds->all())
                            ->orWhereIn('integrated_origin_program_id', $integratedProgramIds->all());
                    })
                    ->when(Schema::hasColumn('student_masters', 'is_deleted'), fn($query) => $query->where('is_deleted', 0))
                    ->when(Schema::hasColumn('student_masters', 'is_left'), fn($query) => $query->where('is_left', 0))
                    ->get(['new_program_id', 'integrated_origin_program_id']);

                $integratedCountsByProgram = [];
                foreach ($integratedStudentRows as $student) {
                    $newProgramId = (int) ($student->new_program_id ?? 0);
                    $originProgramId = (int) ($student->integrated_origin_program_id ?? 0);

                    $bucketProgramId = 0;
                    if ($integratedProgramIds->contains($newProgramId)) {
                        $bucketProgramId = $newProgramId;
                    } elseif ($integratedProgramIds->contains($originProgramId)) {
                        $bucketProgramId = $originProgramId;
                    }

                    if ($bucketProgramId > 0) {
                        $integratedCountsByProgram[$bucketProgramId] = (int) ($integratedCountsByProgram[$bucketProgramId] ?? 0) + 1;
                    }
                }

                $combinations = $combinations->map(function ($combination) use ($integratedCountsByProgram, $integratedProgramIds) {
                    $programId = (int) ($combination->student_program_id ?? 0);
                    if ($integratedProgramIds->contains($programId)) {
                        $combination->studentmaster_count = (int) ($integratedCountsByProgram[$programId] ?? 0);
                    }
                    return $combination;
                })->values();
            }
        }


        $programIds = StudentMaster::where('batch', $activeBatch)
            ->where('campus_id', $subject->campus_id)
            ->when(Schema::hasColumn('student_masters', 'is_left'), fn($query) => $query->where('is_left', 0))
            ->whereNotNull('new_program_id')
            ->distinct()
            ->pluck('new_program_id')
            ->toArray();

        $programs = StudentProgram::where('campus_id', $subject->campus_id)
            ->whereIn('id', $programIds)
            ->orderBy('name')
            ->get();
        $faculties = SubjectFacultyMaster::with('faculty')->where('subject_id', $subjectId)->get();
        $syllabusCount = SyllabusManager::where('subject_id', $subjectId)->distinct('batch_id')->count();




        // Get upcoming activities
        $upcomingActivities = DepartmentActivity::withCount('participants')->where('subject_id', $subjectId)
            ->upcoming()
            ->take(3)
            ->get();

        // Get activity stats
        $activityStats = [
            'total' => DepartmentActivity::where('subject_id', $subjectId)->count(),
            'upcoming' => DepartmentActivity::where('subject_id', $subjectId)->upcoming()->count(),
        ];
        // Subject Combination Master for this department
        $subjectCombinationRows = SubjectCombinationMaster::with(['batch', 'campus', 'mainSubject', 'comboSubject'])
            ->where('main_subject_id', $subjectId)
            ->orWhere('combo_subject_id', $subjectId)
            ->latest()
            ->get();
        $subjectCombinationsGrouped = SubjectCombinationMaster::with(['batch', 'campus', 'mainSubject', 'comboSubject'])
            ->where('main_subject_id', $subjectId)
            ->latest()
            ->get()
            ->groupBy(fn($r) => $r->batch_id . '-' . $r->campus_id . '-' . $r->main_subject_id);
        $allSubjects = Subject::orderBy('title')->get();
        $allCampuses = Campus::all();

        $hasDateRange = $request->filled('attendance_from') || $request->filled('attendance_to');
        $attendanceFrom = null;
        $attendanceTo = null;

        if ($hasDateRange) {
            $attendanceFrom = $request->filled('attendance_from')
                ? Carbon::parse((string) $request->input('attendance_from'))->toDateString()
                : Carbon::parse((string) $request->input('attendance_to'))->toDateString();
            $attendanceTo = $request->filled('attendance_to')
                ? Carbon::parse((string) $request->input('attendance_to'))->toDateString()
                : $attendanceFrom;

            if ($attendanceFrom > $attendanceTo) {
                [$attendanceFrom, $attendanceTo] = [$attendanceTo, $attendanceFrom];
            }
        }

        $attendanceBatch = $request->filled('attendance_batch') ? (int) $request->attendance_batch : null;
        $offeredCourseIds = SubjectCourseMaster::query()
            ->where('subject_id', $subjectId)
            ->whereNotNull('course_master_id')
            ->pluck('course_master_id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $attendanceCourseId = $request->filled('attendance_course_id') ? (int) $request->attendance_course_id : null;
        if ($attendanceCourseId && !$offeredCourseIds->contains($attendanceCourseId)) {
            $attendanceCourseId = null;
        }

        $buildAttendanceQuery = function (bool $applyCourseFilter = false) use (
            $subjectId,
            $subject,
            $attendanceFrom,
            $attendanceTo,
            $attendanceBatch,
            $attendanceCourseId,
            $offeredCourseIds
        ) {
            return StudentAttendance::query()
                ->join('student_masters as sm', 'sm.id', '=', 'student_attendances.student_id')
                ->where('sm.department', $subjectId)
                ->where('sm.campus_id', (int) $subject->campus_id)
                ->when(Schema::hasColumn('student_masters', 'is_left'), fn($query) => $query->where('sm.is_left', 0))
                ->when($offeredCourseIds->isNotEmpty(), fn($query) => $query->whereIn('student_attendances.course_id', $offeredCourseIds->all()))
                ->when($attendanceFrom, fn($query) => $query->whereDate('student_attendances.attendance_date', '>=', $attendanceFrom))
                ->when($attendanceTo, fn($query) => $query->whereDate('student_attendances.attendance_date', '<=', $attendanceTo))
                ->when($attendanceBatch, fn($query) => $query->where('sm.batch', $attendanceBatch))
                ->when($applyCourseFilter && $attendanceCourseId, fn($query) => $query->where('student_attendances.course_id', $attendanceCourseId));
        };

        $courseWiseAttendance = $buildAttendanceQuery(true)
            ->leftJoin('program_course_masters as pcm', 'pcm.id', '=', 'student_attendances.course_id')
            ->whereNotNull('student_attendances.course_id')
            ->groupBy('student_attendances.course_id', 'pcm.course_code', 'pcm.course_title')
            ->orderBy('pcm.course_title')
            ->select([
                'student_attendances.course_id',
                'pcm.course_code',
                'pcm.course_title',
                DB::raw('COUNT(*) as total_records'),
                DB::raw("SUM(CASE WHEN student_attendances.status IN ('present','late','excused') THEN 1 ELSE 0 END) as attended_records"),
            ])
            ->get()
            ->map(function ($row) {
                $total = (int) ($row->total_records ?? 0);
                $attended = (int) ($row->attended_records ?? 0);
                $percentage = $total > 0 ? round(($attended / $total) * 100, 2) : 0;
                return [
                    'course_id' => (int) ($row->course_id ?? 0),
                    'course_label' => trim(((string) ($row->course_code ?? '')) . ' - ' . ((string) ($row->course_title ?? '')), ' -') ?: 'Unknown Course',
                    'attendance_percentage' => $percentage,
                    'attended_records' => $attended,
                    'total_records' => $total,
                ];
            })
            ->values();

        $dateWiseAttendance = $buildAttendanceQuery(true)
            ->groupBy('student_attendances.attendance_date')
            ->orderBy('student_attendances.attendance_date')
            ->select([
                'student_attendances.attendance_date',
                DB::raw('COUNT(*) as total_records'),
                DB::raw("SUM(CASE WHEN student_attendances.status IN ('present','late','excused') THEN 1 ELSE 0 END) as attended_records"),
            ])
            ->get()
            ->map(function ($row) {
                $total = (int) ($row->total_records ?? 0);
                $attended = (int) ($row->attended_records ?? 0);
                return [
                    'date' => Carbon::parse($row->attendance_date)->format('d M Y'),
                    'attendance_percentage' => $total > 0 ? round(($attended / $total) * 100, 2) : 0,
                    'attended_records' => $attended,
                    'total_records' => $total,
                ];
            })
            ->values();

        $overallAttendanceTotals = $buildAttendanceQuery(true)
            ->select([
                DB::raw('COUNT(*) as total_records'),
                DB::raw("SUM(CASE WHEN student_attendances.status IN ('present','late','excused') THEN 1 ELSE 0 END) as attended_records"),
            ])
            ->first();

        $overallTotalRecords = (int) ($overallAttendanceTotals->total_records ?? 0);
        $overallAttendedRecords = (int) ($overallAttendanceTotals->attended_records ?? 0);
        $overallAttendancePercentage = $overallTotalRecords > 0 ? round(($overallAttendedRecords / $overallTotalRecords) * 100, 2) : 0;

        $buildAttendanceAlertQuery = function () use (
            $subjectId,
            $subject,
            $offeredCourseIds
        ) {
            return StudentAttendance::query()
                ->join('student_masters as sm', 'sm.id', '=', 'student_attendances.student_id')
                ->where('sm.department', $subjectId)
                ->where('sm.campus_id', (int) $subject->campus_id)
                ->when(Schema::hasColumn('student_masters', 'is_left'), fn($query) => $query->where('sm.is_left', 0))
                ->when($offeredCourseIds->isNotEmpty(), fn($query) => $query->whereIn('student_attendances.course_id', $offeredCourseIds->all()));
        };

        $attendanceAlertStudents = $buildAttendanceAlertQuery()
            ->leftJoin('student_program as sp', 'sp.id', '=', 'sm.new_program_id')
            ->groupBy('sm.id', 'sm.roll_no', 'sm.first_name', 'sm.last_name', 'sp.name')
            ->select([
                'sm.id as student_id',
                'sm.roll_no',
                'sm.first_name',
                'sm.last_name',
                'sp.name as program_name',
                DB::raw('COUNT(*) as total_records'),
                DB::raw("SUM(CASE WHEN student_attendances.status IN ('present','late','excused') THEN 1 ELSE 0 END) as attended_records"),
            ])
            ->get()
            ->map(function ($row) {
                $total = (int) ($row->total_records ?? 0);
                $attended = (int) ($row->attended_records ?? 0);
                $percentage = $total > 0 ? round(($attended / $total) * 100, 2) : 0;
                return [
                    'student_id' => (int) ($row->student_id ?? 0),
                    'roll_no' => (string) ($row->roll_no ?? '-'),
                    'student_name' => trim(((string) ($row->first_name ?? '')) . ' ' . ((string) ($row->last_name ?? ''))) ?: '-',
                    'program_name' => (string) ($row->program_name ?? '-'),
                    'attendance_percentage' => $percentage,
                    'attended_records' => $attended,
                    'total_records' => $total,
                ];
            })
            ->filter(fn($row) => (float) $row['attendance_percentage'] < 75)
            ->sortBy('attendance_percentage')
            ->values();

        $belowThresholdCount = $attendanceAlertStudents->count();

        $attendanceCourses = ProgramCourseMaster::query()
            ->whereIn('id', $offeredCourseIds->all())
            ->orderBy('course_title')
            ->get(['id', 'course_code', 'course_title']);

        return view('admin.subject.department-dashboard', [
            'data' => $courseMaster,
            'students_count' => $studentsCount,
            'semesters_count' => $semestersCount,
            'batchWiseStudents' => $batchWiseStudents,
            'combinations' => $combinations,
            'programs' => $programs,
            'activeBatch' => $activeBatch,
            'deptfaculties' => $faculties,
            'syllabusCount' => $syllabusCount,
            'upcomingActivities' => $upcomingActivities,
            'activityStats' => $activityStats,
            'subjectCombinationsGrouped' => $subjectCombinationsGrouped,
            'allSubjects' => $allSubjects,
            'allCampuses' => $allCampuses,
            'allBatches' => $batches,
            'attendanceFrom' => $attendanceFrom,
            'attendanceTo' => $attendanceTo,
            'attendanceBatch' => $attendanceBatch,
            'attendanceCourseId' => $attendanceCourseId,
            'attendanceCourses' => $attendanceCourses,
            'courseWiseAttendance' => $courseWiseAttendance,
            'dateWiseAttendance' => $dateWiseAttendance,
            'overallAttendancePercentage' => $overallAttendancePercentage,
            'overallAttendedRecords' => $overallAttendedRecords,
            'overallTotalRecords' => $overallTotalRecords,
            'attendanceAlertStudents' => $attendanceAlertStudents,
            'attendanceAlertCount' => $attendanceAlertStudents->count(),
            'belowThresholdCount' => $belowThresholdCount,
        ]);
    }

    function exportDepartmentAttendanceAlerts(Request $request)
    {
        $userId = Auth::id();
        $subjectId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
        $subject = Subject::find($subjectId);

        if (!$subject) {
            return redirect()->route('department.dashboard')->with('info', 'Subject not found or user not assigned to any department');
        }
        $offeredCourseIds = SubjectCourseMaster::query()
            ->where('subject_id', $subjectId)
            ->whereNotNull('course_master_id')
            ->pluck('course_master_id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $buildAttendanceQuery = function () use (
            $subjectId,
            $subject,
            $offeredCourseIds
        ) {
            return StudentAttendance::query()
                ->join('student_masters as sm', 'sm.id', '=', 'student_attendances.student_id')
                ->where('sm.department', $subjectId)
                ->where('sm.campus_id', (int) $subject->campus_id)
                ->when(Schema::hasColumn('student_masters', 'is_left'), fn($query) => $query->where('sm.is_left', 0))
                ->when($offeredCourseIds->isNotEmpty(), fn($query) => $query->whereIn('student_attendances.course_id', $offeredCourseIds->all()));
        };

        $rows = $buildAttendanceQuery()
            ->leftJoin('student_program as sp', 'sp.id', '=', 'sm.new_program_id')
            ->groupBy('sm.id', 'sm.roll_no', 'sm.first_name', 'sm.last_name', 'sp.name')
            ->select([
                'sm.id as student_id',
                'sm.roll_no',
                'sm.first_name',
                'sm.last_name',
                'sp.name as program_name',
                DB::raw('COUNT(*) as total_records'),
                DB::raw("SUM(CASE WHEN student_attendances.status IN ('present','late','excused') THEN 1 ELSE 0 END) as attended_records"),
            ])
            ->get()
            ->map(function ($row) {
                $total = (int) ($row->total_records ?? 0);
                $attended = (int) ($row->attended_records ?? 0);
                $percentage = $total > 0 ? round(($attended / $total) * 100, 2) : 0;
                if ($percentage >= 75) {
                    return null;
                }

                return [
                    'student_id' => (int) ($row->student_id ?? 0),
                    'roll_no' => (string) ($row->roll_no ?? '-'),
                    'student_name' => trim(((string) ($row->first_name ?? '')) . ' ' . ((string) ($row->last_name ?? ''))) ?: '-',
                    'program_name' => (string) ($row->program_name ?? '-'),
                    'attended_records' => $attended,
                    'total_records' => $total,
                    'attendance_percentage' => $percentage,
                ];
            })
            ->filter()
            ->sortBy('attendance_percentage')
            ->values();

        $fileName = 'attendance_alert_students_' . Carbon::now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Student ID',
                'Roll No',
                'Student Name',
                'Program',
                'Present Records',
                'Total Records',
                'Current Attendance %',
            ]);

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['student_id'],
                    $row['roll_no'],
                    $row['student_name'],
                    $row['program_name'],
                    $row['attended_records'],
                    $row['total_records'],
                    number_format((float) $row['attendance_percentage'], 2),
                ]);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    function studentAttendanceDetails(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:student_masters,id',
            'rollno' => 'required|string',
            'attendance_from' => 'nullable|date',
            'attendance_to' => 'nullable|date',
            'attendance_course_id' => 'nullable|integer|exists:program_course_masters,id',
        ]);

        $userId = Auth::id();
        $subjectId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
        $subject = Subject::find($subjectId);

        if (!$subject) {
            return redirect()->route('department.dashboard')->with('info', 'Subject not found or user not assigned to any department');
        }

        $student = StudentMaster::query()
            ->where('id', (int) $request->id)
            ->where('roll_no', (string) $request->rollno)
            ->where('department', $subjectId)
            ->where('campus_id', (int) $subject->campus_id)
            ->firstOrFail(['id', 'roll_no', 'first_name', 'last_name', 'new_program_id', 'batch']);

        $offeredCourseIds = SubjectCourseMaster::query()
            ->where('subject_id', $subjectId)
            ->whereNotNull('course_master_id')
            ->pluck('course_master_id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $attendanceCourseId = $request->filled('attendance_course_id') ? (int) $request->attendance_course_id : null;
        if ($attendanceCourseId && !$offeredCourseIds->contains($attendanceCourseId)) {
            $attendanceCourseId = null;
        }

        $hasDateRange = $request->filled('attendance_from') || $request->filled('attendance_to');
        $attendanceFrom = null;
        $attendanceTo = null;

        if ($hasDateRange) {
            $attendanceFrom = $request->filled('attendance_from')
                ? Carbon::parse((string) $request->input('attendance_from'))->toDateString()
                : Carbon::parse((string) $request->input('attendance_to'))->toDateString();
            $attendanceTo = $request->filled('attendance_to')
                ? Carbon::parse((string) $request->input('attendance_to'))->toDateString()
                : $attendanceFrom;

            if ($attendanceFrom > $attendanceTo) {
                [$attendanceFrom, $attendanceTo] = [$attendanceTo, $attendanceFrom];
            }
        }

        $attendanceCourses = ProgramCourseMaster::query()
            ->whereIn('id', $offeredCourseIds->all())
            ->orderBy('course_title')
            ->get(['id', 'course_code', 'course_title']);

        $baseAttendanceQuery = StudentAttendance::query()
            ->where('student_id', (int) $student->id)
            ->when($attendanceFrom, fn($query) => $query->whereDate('attendance_date', '>=', $attendanceFrom))
            ->when($attendanceTo, fn($query) => $query->whereDate('attendance_date', '<=', $attendanceTo))
            ->when($offeredCourseIds->isNotEmpty(), fn($query) => $query->whereIn('course_id', $offeredCourseIds->all()))
            ->when($attendanceCourseId, fn($query) => $query->where('course_id', $attendanceCourseId));

        $attendanceSummaryByCourse = (clone $baseAttendanceQuery)
            ->leftJoin('program_course_masters as pcm', 'pcm.id', '=', 'student_attendances.course_id')
            ->groupBy('student_attendances.course_id', 'pcm.course_code', 'pcm.course_title')
            ->orderBy('pcm.course_title')
            ->select([
                'student_attendances.course_id',
                'pcm.course_code',
                'pcm.course_title',
                DB::raw('COUNT(*) as total_records'),
                DB::raw("SUM(CASE WHEN student_attendances.status IN ('present','late','excused') THEN 1 ELSE 0 END) as attended_records"),
            ])
            ->get()
            ->map(function ($row) {
                $total = (int) ($row->total_records ?? 0);
                $attended = (int) ($row->attended_records ?? 0);
                return [
                    'course_label' => trim(((string) ($row->course_code ?? '')) . ' - ' . ((string) ($row->course_title ?? '')), ' -') ?: 'Unknown Course',
                    'attended_records' => $attended,
                    'total_records' => $total,
                    'attendance_percentage' => $total > 0 ? round(($attended / $total) * 100, 2) : 0,
                ];
            })
            ->values();

        $attendanceTimeline = (clone $baseAttendanceQuery)
            ->leftJoin('program_course_masters as pcm', 'pcm.id', '=', 'student_attendances.course_id')
            ->orderBy('student_attendances.attendance_date', 'desc')
            ->orderBy('student_attendances.id', 'desc')
            ->get([
                'student_attendances.attendance_date',
                'student_attendances.status',
                'pcm.course_code',
                'pcm.course_title',
            ]);

        $totalRecords = $attendanceTimeline->count();
        $attendedRecords = $attendanceTimeline->whereIn('status', ['present', 'late', 'excused'])->count();
        $overallPercentage = $totalRecords > 0 ? round(($attendedRecords / $totalRecords) * 100, 2) : 0;

        return view('admin.subject.student-attendance-details', [
            'student' => $student,
            'subject' => $subject,
            'hasDateRange' => $hasDateRange,
            'attendanceFrom' => $attendanceFrom,
            'attendanceTo' => $attendanceTo,
            'attendanceCourseId' => $attendanceCourseId,
            'attendanceCourses' => $attendanceCourses,
            'attendanceSummaryByCourse' => $attendanceSummaryByCourse,
            'attendanceTimeline' => $attendanceTimeline,
            'totalRecords' => $totalRecords,
            'attendedRecords' => $attendedRecords,
            'overallPercentage' => $overallPercentage,
        ]);
    }

    function fetchEnrolledProgramsByBatch(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|integer|exists:batch_masters,id',
            'campus_id' => 'required|integer|exists:campuses,id',
            'subject_id' => 'required|integer|exists:subjects,id',
        ]);

        $subject = Subject::find($request->subject_id);
        if (!$subject) {
            return response()->json([
                'success' => false,
                'programs' => [],
                'message' => 'Subject not found',
            ], 404);
        }

        $campusId = (int) $request->campus_id;
        if ($campusId !== (int) $subject->campus_id) {
            return response()->json([
                'success' => true,
                'programs' => [],
                'message' => 'Campus does not match subject campus',
            ]);
        }

        // Step 1: source enrolled program ids directly from student_masters
        // for selected batch + campus where new_program_id is present.
        $enrolledProgramIds = StudentMaster::where('batch', (int) $request->batch_id)
            ->where('campus_id', $campusId)
            ->when(Schema::hasColumn('student_masters', 'is_left'), fn($query) => $query->where('is_left', 0))
            ->whereNotNull('new_program_id')
            ->distinct()
            ->pluck('new_program_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        if (empty($enrolledProgramIds)) {
            return response()->json([
                'success' => true,
                'programs' => [],
            ]);
        }

        // Step 2: return only matching student_program rows for the same campus.
        $programsQuery = StudentProgram::where('campus_id', $campusId)
            ->whereIn('id', $enrolledProgramIds);

        $programs = $programsQuery
            ->with('programtypemaster:id,name')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'program_type'])
            ->map(function ($program) {
                return [
                    'id' => (int) $program->id,
                    'code' => (string) ($program->code ?? ''),
                    'name' => (string) ($program->name ?? ''),
                    'program_type' => (string) ($program->program_type ?? ''),
                    'program_type_name' => (string) ($program->programtypemaster->name ?? ''),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'programs' => $programs,
        ]);
    }

    function deleteCombination($id)
    {
        $combination = SubjectHasStudentProgam::with('batchmaster')->findOrFail($id);
        //batch Name
        $batch = $combination->batchmaster->batch_name;
        $campus_id = $combination->campus_id;
        //check any application is using this combination
        $applicationCheck =  AdmissionApplication::where('course', $combination->student_program_id)
            ->whereHas('registrationmaster', function ($query) use ($batch, $campus_id) {
                $query->where('batch', $batch)
                    ->where('campus_id', $campus_id);
            })->count();

        if ($applicationCheck > 0) {
            return redirect()->back()->with('info', 'Cannot delete combination. We have ' . $applicationCheck . ' application(s) using this combination');
        } else {
            $combination->delete();
            return redirect()->back()->with('success', 'Combination Deleted');
        }
    }

    function updateCombination(Request $request, $id)
    {

        $combination = SubjectHasStudentProgam::findOrFail($id);
        $activeBatch = $combination->batch_id;

        $enrolledCount = SubjectHasStudentProgam::where('id', $combination->id)
            ->withCount(['studentmaster' => function ($query) use ($activeBatch) {
                $query->where('batch', $activeBatch)
                    ->when(Schema::hasColumn('student_masters', 'is_left'), fn($inner) => $inner->where('is_left', 0));
            }])
            ->value('studentmaster_count');

        //updating

        $request->validate([
            'shift_id' => 'nullable|exists:shift_masters,slug',
        ]);


        $combination->total_seats = $request->total_seats;
        $combination->shift = $request->filled('shift_id') ? $request->shift_id : null;
        $combination->total_available_seats = $request->filled('total_seats') ? (int) $request->total_seats  - $enrolledCount : null;
        $combination->save();

        return redirect()->back()->with('success', 'Combination Updated');
    }

    function updateCombinationSpecializations(Request $request, $id)
    {
        $combination = SubjectHasStudentProgam::findOrFail($id);

        if (Schema::hasTable('integrated_program_sublayer_settings')) {
            $isIntegratedProgram = DB::table('integrated_program_sublayer_settings')
                ->where('student_program_id', (int) ($combination->student_program_id ?? 0))
                ->where('is_active', 1)
                ->exists();

            if ($isIntegratedProgram) {
                return redirect()->back()->with('error', 'Specialization management is disabled for integrated programs. Manage specialization in sublayer programs.');
            }
        }

        $request->validate([
            'specialization_ids' => 'nullable|array',
            'specialization_ids.*' => [
                'integer',
                Rule::exists('specialization_masters', 'id')->where(function ($query) use ($combination) {
                    $query->where('subject_id', $combination->subject_id);
                }),
            ],
        ]);

        $specializationIds = collect($request->input('specialization_ids', []))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $combination->specialization_ids = $specializationIds;
        $combination->save();

        return redirect()->back()->with('success', 'Program specializations updated successfully.');
    }

    function courseMaster(int $academicDeptId, $slug)
    {
        $data = Subject::find($academicDeptId);

        if (!$data) {
            return redirect()->back()->with('error', 'Department not found');
        }

        $courses = SubjectCourseMaster::where('subject_id', $academicDeptId)
            ->with(['courseMaster' => function ($query) {
                $query->withTrashed();
            }])
            ->get();

        $assignedCourseIds = SubjectCourseMaster::where('subject_id', $academicDeptId)
            ->pluck('course_master_id')
            ->toArray();

        $unassignedCoursesQuery = ProgramCourseMaster::where(function ($query) {
            $query->whereNull('is_deleted')->orWhere('is_deleted', 0);
        });

        if (!empty($assignedCourseIds)) {
            $unassignedCoursesQuery->whereNotIn('id', $assignedCourseIds);
        }

        $unassignedCourses = $unassignedCoursesQuery->with('coursetypemaster')->get();

        return view('admin.subject.course-master', [
            'data' => $data,
            'course_master' => $unassignedCourses,
            'mycourses' => $courses,
        ]);
    }


    function addCourseMaster(Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'courses' => 'required|array|min:1',
        ]);

        $courseMasterId =   $request->courses;

        for ($i = 0; $i < count($courseMasterId); $i++) {
            $courseId = $courseMasterId[$i];
            $existingRecord = SubjectCourseMaster::where('subject_id', $request->subject_id)
                ->where('course_master_id', $courseId)
                ->first();

            if (!$existingRecord) {
                $rec = new SubjectCourseMaster();
                $rec->subject_id = $request->subject_id;
                $rec->course_master_id = $courseId;
                $rec->save();
            }
        }


        return redirect()->back()->with('success', 'Course Master Added');
    }

    function deleteCourseMaster($id)
    {
        $record = SubjectCourseMaster::find($id);
        if ($record != null) {
            $record->delete();
            return redirect()->back()->with('success', 'Course Unlinked Successfully');
        } else {
            return redirect()->back()->with('error', 'Course not found');
        }
        /*
        $record = SubjectCourseMaster::with('courseMaster')->findOrFail($id);
        $courseMasterId = $record->course_master_id;
        $subjectCourseId = $record->id;

        // Check if course has CSOs
        $hasCsos = CoHasCso::where('co_id', $courseMasterId)->exists();

        // Check if course has attendance records
        $hasAttendance = StudentAttendance::where('course_id', $courseMasterId)->exists();

        // Check if course has timetable entries
        $hasTimetable = SubjectHasRoutine::where('subject_course_id', $subjectCourseId)->exists();

        // Check if course has syllabus entries
        $hasSyllabus = SyllabusManager::where('co_id', $courseMasterId)->exists();

        if ($hasCsos || $hasAttendance || $hasTimetable || $hasSyllabus) {
            $reasons = [];
            if ($hasCsos) $reasons[] = 'course objectives (CSO) are defined';
            if ($hasAttendance) $reasons[] = 'attendance records exist';
            if ($hasTimetable) $reasons[] = 'timetable entries exist';
            if ($hasSyllabus) $reasons[] = 'syllabus entries exist';

            return redirect()->back()->with(
                'error',
                'Cannot delete this course — ' . implode(', ', $reasons) . '. Please clear those first.'
            );
        }

        $record->delete();
        
        return redirect()->back()->with('success', 'Course Unlinked Successfully');
        */
    }


    function adminCourseMaster(Request $request)
    {
        $courseTypeFilter = $request->input('course_type');

        if ($courseTypeFilter) {
            $data = ProgramCourseMaster::with([
                'coursetypemaster',
                'semestermaster',
            ])->whereHas('coursetypemaster', function ($query) use ($courseTypeFilter) {
                $query->where('id', $courseTypeFilter);
            })->withCount(['stucourseinfo' => function ($query) {
                $query->where('is_deleted', 0);
                $query->where('campus_id', 2);
            }])->get();
        } else {

            $data = ProgramCourseMaster::with([
                'coursetypemaster',
                'semestermaster',
            ])->withCount(['stucourseinfo' => function ($query) {
                $query->where('is_deleted', 0);
                $query->where('campus_id', 2);
            }])->get();
        }
        return view('admin.master.course-master', ['data' => $data]);
    }


    function deptAllCourseCombinations(Request $request)
    {

        $combinations = SubjectHasStudentProgam::with([
            'subjectmaster',
            'studentprograminfo',
            'batchmaster',
            'campusmaster',
        ])->get();

        return view('admin.subject.all-combination', ['combinations' => $combinations]);
    }


    function deleteSemesterFromSubject($id)
    {
        $record = SubjectHasSemester::findOrFail($id);
        $record->delete();
        return redirect()->back()->with('success', 'Semester Removed from Subject');
    }

    function addFacultyMasterToSubject(Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'faculty' => 'required|array|min:1',
        ]);

        $facultyIds =   $request->faculty;

        for ($i = 0; $i < count($facultyIds); $i++) {
            $facultyId = $facultyIds[$i];

            $existingRecord = SubjectFacultyMaster::where('subject_id', $request->subject_id)
                ->where('faculty_id', $facultyId)
                ->first();

            if (!$existingRecord) {
                $rec = new SubjectFacultyMaster();
                $rec->subject_id = $request->subject_id;
                $rec->faculty_id = $facultyId;
                $rec->save();
            }
        }

        return redirect()->back()->with('success', 'Faculty Added ');
    }

    function deleteFacultyMasterFromSubject($id)
    {
        $record = SubjectFacultyMaster::findOrFail($id);
        $record->delete();
        return redirect()->back()->with('success', 'Faculty Removed from Subject');
    }

    function studentProgramMaster()
    {
        $data = StudentProgram::with(['programgroup', 'programtypemaster', 'combomap.combo1:id,title', 'combomap.combo2:id,title', 'shiftmaster'])
            ->latest()->get()
            ->map(function ($program) {
                $program->student_count = StudentMaster::where('new_program_id', $program->id)->count();
                return $program;
            });

        $shiftOptions = ShiftMaster::where('is_active', 1)
            ->orderBy('sort_order')
            ->get(['title', 'slug']);

        return view('admin.subject.student-program-master', [
            'data' => $data,
            'shiftOptions' => $shiftOptions,
        ]);
    }

    function addNewStudentProgram(Request $request)
    {
        $allowedShifts = $this->getShiftSlugs();
        $request->validate([
            'campus' => 'required',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'semester_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'shift' => ['nullable', Rule::in($allowedShifts)],
        ]);

        $rec = new StudentProgram();
        $rec->campus_id = $request->campus;
        $rec->shift = $request->shift ?: $this->getDefaultShiftSlug();
        $rec->code = Str::upper($request->code);
        $rec->name = Str::lower($request->name);
        $rec->description = Str::lower($request->description);
        $rec->semester_count = $request->semester_count;
        $rec->save();

        return redirect()->back()->with('success', 'New Program Added');
    }

    function updateAcademicDept(Request $request, $id)
    {
        $data = Subject::findOrFail($id);
        $request->validate([
            'campus' => 'required',
            'program_id' => 'required',
        ]);


        $data->campus_id = $request->campus;
        $data->main_program_type = $request->program_id;
        $data->code = $request->code;
        $data->title = $request->title;
        $data->save();

        return redirect()->back()->with('success', 'Academic Department Updated');
    }

    function updateStudentProgram(Request $request, $id)
    {
        // return $request->all();
        $data = StudentProgram::findOrFail($id);
        $allowedShifts = $this->getShiftSlugs();
        $request->validate([
            'campus' => 'required',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'semester_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'program_type' => 'required',
            'shift' => ['nullable', Rule::in($allowedShifts)],
        ]);

        $data->campus_id = $request->campus;
        $data->shift = $request->shift ?: $this->getDefaultShiftSlug();
        $data->code = Str::upper($request->code);
        $data->name = $request->name;
        $data->description = $request->description;
        $data->semester_count = $request->semester_count;
        $data->program_type = $request->program_type;
        $data->save();


        if (!empty($request->combo_id_1) && !empty($request->combo_id_2)) {
            $existingCombo = StdProgComboMap::where('student_program_id', $id)->first();

            if ($existingCombo) {
                $existingCombo->combo_id_1 = $request->combo_id_1;
                $existingCombo->combo_id_2 = $request->combo_id_2;
                $existingCombo->save();
            } else {
                StdProgComboMap::create([
                    'student_program_id' => $id,
                    'combo_id_1' => $request->combo_id_1,
                    'combo_id_2' => $request->combo_id_2,
                ]);
            }
        }

        $data->load(['programtypemaster', 'combomap.combo1:id,title', 'combomap.combo2:id,title', 'shiftmaster']);

        if ($request->ajax() || $request->expectsJson()) {
            $programTypeName = $data->programtypemaster->name ?? 'Unknown';
            $comboLabel = ($data->combomap->combo1->title ?? 'Unknown') . ' - ' . ($data->combomap->combo2->title ?? 'Unknown');

            return response()->json([
                'success' => true,
                'message' => 'Program Updated',
                'data' => [
                    'id' => (int) $data->id,
                    'campus_id' => (int) $data->campus_id,
                    'campus_label' => (int) $data->campus_id === 1 ? 'Sonada' : 'Siliguri Campus',
                    'code' => (string) $data->code,
                    'name' => (string) $data->name,
                    'shift' => (string) ($data->shiftmaster->title ?? $data->shift ?? 'common'),
                    'description' => (string) ($data->description ?? ''),
                    'semester_count' => (int) $data->semester_count,
                    'program_type' => (string) $programTypeName,
                    'combo_label' => (string) $comboLabel,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Program Updated');
    }

    function addNewCourseMaster(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'course_code' => 'required|string|max:100',
            'course_title' => 'required|string|max:255',
            'course_type' => 'required',
            'internal' => 'required|numeric|min:0',
            'external' => 'required|numeric|min:0',
            'credits' => 'required|numeric|min:0',
        ]);

        $normalizedCourseCode = Str::upper(trim((string) $request->course_code));
        $courseCodeExists = ProgramCourseMaster::query()
            ->whereRaw('UPPER(TRIM(course_code)) = ?', [$normalizedCourseCode])
            ->exists();

        if ($courseCodeExists) {
            return redirect()->back()->with('error', 'Course code already exists. Course codes must be unique.');
        }

        $rec = new ProgramCourseMaster();
        $rec->academic_year = (string) Carbon::now()->year;
        $rec->course_code = $normalizedCourseCode;
        $rec->course_title = $request->course_title;
        $rec->course_type = $request->course_type;
        $rec->department = $request->subject_id;
        $rec->internal = $request->internal;
        $rec->external =  $request->external;
        $rec->total = $request->internal + $request->external;
        $rec->credits = $request->credits;
        $rec->paper_type_id = $request->paper_type;
        $rec->total_alloted_hours = $request->total_alloted_hours;
        $rec->is_deleted = 0;
        $rec->save();

        SubjectCourseMaster::firstOrCreate([
            'subject_id' => $request->subject_id,
            'course_master_id' => $rec->id,
        ]);

        return redirect()->back()->with('success', 'New Course Master Added and Can be Used in Departments Now');
    }

    function checkCourseCodeAvailability(Request $request)
    {
        $validated = $request->validate([
            'course_code' => 'required|string|max:100',
            'exclude_id' => 'nullable|integer|min:1',
        ]);

        $normalizedCourseCode = Str::upper(trim((string) $validated['course_code']));
        $excludeId = (int) ($validated['exclude_id'] ?? 0);

        if ($normalizedCourseCode === '') {
            return response()->json([
                'available' => false,
                'message' => 'Course code is required.',
            ], 422);
        }

        $existsQuery = ProgramCourseMaster::query()
            ->whereRaw('UPPER(TRIM(course_code)) = ?', [$normalizedCourseCode]);

        if ($excludeId > 0) {
            $existsQuery->where('id', '!=', $excludeId);
        }

        $exists = $existsQuery->exists();

        return response()->json([
            'available' => !$exists,
            'normalized_code' => $normalizedCourseCode,
            'message' => $exists
                ? 'Course code already exists. It cannot be created.'
                : 'Course code is available.',
        ]);
    }

    function viewCourseSpecificObjective($courseId)
    {
        $defaultShift = $this->getDefaultShiftSlug();
        $course = SubjectCourseMaster::with([
            'courseMaster.coursetypemaster',
            'courseMaster.csos.csosubunits.taxonomies.rbtmaster',
        ])->where('course_master_id', $courseId)->first();

        if (!$course) {
            return redirect()->back()->with('error', 'Course not found');
        }

        if ($course->courseMaster && $course->courseMaster->relationLoaded('csos')) {
            $filteredCsos = $course->courseMaster->csos
                ->whereIn('shift', [$defaultShift, null])
                ->values();

            $course->courseMaster->setRelation('csos', $this->dedupeCsosByTitle($filteredCsos));
        }

        $objectives = PoHasCo::where('co_id', $courseId)->get();
        $shiftOptions = ShiftMaster::where('is_active', 1)
            ->orderBy('sort_order')
            ->get(['title', 'slug']);

        return view('admin.subject.course-objective', [
            'course' => $course,
            'objectives' => $objectives,
            'shiftOptions' => $shiftOptions,
            'subjectUsesShifts' => false,
        ]);
    }

    function downloadCourseSpecificObjectivePdf(Request $request, $courseId)
    {
        $defaultShift = $this->getDefaultShiftSlug();
        $selectedShift = (string) $request->query('shift', 'all');

        $course = SubjectCourseMaster::with([
            'courseMaster.coursetypemaster',
            'courseMaster.csos.csosubunits.taxonomies.rbtmaster',
        ])->where('course_master_id', $courseId)->first();

        if (!$course || !$course->courseMaster) {
            return redirect()->back()->with('error', 'Course not found.');
        }

        $allCsos = $course->courseMaster->csos ?? collect();
        if ($selectedShift !== 'all') {
            $allCsos = $allCsos->where('shift', $selectedShift)->values();
        }

        // Keep backward-compatible default filtering when shift is not explicitly selected.
        if ($selectedShift === 'all') {
            $allCsos = $allCsos->whereIn('shift', [$defaultShift, null, ''])->values();
        }

        $filteredCsos = $this->dedupeCsosByTitle($allCsos);

        $pdf = Pdf::loadView('admin.subject.course-objective-pdf', [
            'course' => $course,
            'filteredCsos' => $filteredCsos,
            'selectedShift' => $selectedShift,
        ])->setPaper('a4', 'portrait');

        $courseCode = trim((string) ($course->courseMaster->course_code ?? 'course'));
        $safeCode = preg_replace('/[^A-Za-z0-9\-_]/', '_', $courseCode);
        $suffix = $selectedShift !== 'all' ? ('-' . strtolower($selectedShift)) : '';
        $filename = 'course-objectives-' . $safeCode . $suffix . '-' . date('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    function getCsoListForCourse(Request $request, $courseId)
    {
        $defaultShift = $this->getDefaultShiftSlug();
        $subject = Subject::find((int) $request->input('subject_id', 0));
        $subjectUsesShifts = $subject ? ((int) ($subject->has_shift_delivery ?? 0) === 1) : false;
        $allowedShiftSlugs = $this->getSubjectAllowedShiftSlugs($subject);
        $requestedShift = (string) $request->input('shift', '');

        $selectedShift = $defaultShift;
        if ($subjectUsesShifts && !empty($allowedShiftSlugs)) {
            $selectedShift = in_array($requestedShift, $allowedShiftSlugs, true)
                ? $requestedShift
                : ((string) ($allowedShiftSlugs[0] ?? $defaultShift));
        }

        $query = CoHasCso::with(['csosubunits.taxomonylevel'])
            ->where('co_id', $courseId);

        if ($subjectUsesShifts && !empty($allowedShiftSlugs)) {
            $candidateShifts = collect($allowedShiftSlugs)
                ->prepend($defaultShift)
                ->prepend($selectedShift)
                ->filter()
                ->map(fn($shift) => (string) $shift)
                ->unique()
                ->values()
                ->all();

            $query->where(function ($q) use ($candidateShifts) {
                $q->whereIn('shift', $candidateShifts)
                    ->orWhereNull('shift')
                    ->orWhere('shift', '');
            });
        } else {
            $query->where(function ($q) use ($defaultShift) {
                $q->where('shift', $defaultShift)
                    ->orWhereNull('shift')
                    ->orWhere('shift', '');
            });
        }

        $csos = $this->dedupeCsosByTitle($query->orderBy('id')->get());

        // Backward compatibility: legacy CSOs may exist with shift values outside current filters.
        if ($csos->isEmpty()) {
            $csos = $this->dedupeCsosByTitle(
                CoHasCso::with(['csosubunits.taxomonylevel'])
                    ->where('co_id', $courseId)
                    ->orderBy('id')
                    ->get()
            );
        }

        if ($csos->isEmpty()) {
            return response()->json([]);
        }

        return response()->json($csos);
    }

    function createCourseSpecificObjective(Request $request)
    {
        $allowedShifts = $this->getShiftSlugs();
        $validated = $request->validate([
            'title' => 'required',
            'course_id' => 'required',
            'lectures_needed' => 'required',
            'shift' => ['nullable', Rule::in($allowedShifts)],
        ]);

        $normalizedTitle = $this->normalizeTitle($request->title);
        $defaultShift = $this->getDefaultShiftSlug();

        $hasDuplicate = CoHasCso::where('co_id', $request->course_id)
            ->where(function ($q) use ($defaultShift) {
                $q->where('shift', $defaultShift)->orWhereNull('shift');
            })
            ->get(['title'])
            ->contains(fn($cso) => $this->normalizeTitle($cso->title) === $normalizedTitle);

        if ($hasDuplicate) {
            return redirect()->back()->with('error', 'This unit already exists for the selected course and shift.');
        }

        CoHasCso::create([
            'co_id' => $request->course_id,
            'title' => $request->title,
            'lectures_needed' => $request->lectures_needed,
            'shift' => $defaultShift,
        ]);

        return redirect()->back()->with('success', 'CSO added successfully');
    }

    function updateCourseMaster(Request $request, $id)
    {


        $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'course_code' => 'required|string|max:255',
            'course_title' => 'required|string|max:255',
            'course_type' => 'required',
            'paper_type' => 'required',
            'co_cso_not_applicable' => 'nullable|in:0,1',
            'co_cso_not_applicable_note' => 'nullable|string|max:255',
        ]);

        $normalizedCourseCode = Str::upper(trim((string) $request->course_code));
        $courseCodeExists = ProgramCourseMaster::query()
            ->whereRaw('UPPER(TRIM(course_code)) = ?', [$normalizedCourseCode])
            ->where('id', '!=', (int) $id)
            ->exists();

        if ($courseCodeExists) {
            return redirect()->back()->with('error', 'Course code already exists. Course codes must be unique.');
        }

        ProgramCourseMaster::where('id', $id)->update([
            'course_code' => $normalizedCourseCode,
            'course_title' => $request->course_title,
            'course_type' => $request->course_type,
            'credits' => $request->credits,
            'internal' => $request->internal,
            'external' => $request->external,
            'total' => $request->internal + $request->external,
            'total_alloted_hours' => $request->total_alloted_hours,
            'paper_type_id' => $request->paper_type,
        ]);

        $subjectCourseMap = SubjectCourseMaster::query()
            ->where('subject_id', (int) $request->subject_id)
            ->where('course_master_id', (int) $id)
            ->first();

        if ($subjectCourseMap && Schema::hasColumn('subject_course_masters', 'co_cso_not_applicable')) {
            $isNoCoCsoApplicable = (int) $request->input('co_cso_not_applicable', 0) === 1;
            $subjectCourseMap->co_cso_not_applicable = $isNoCoCsoApplicable;

            if (Schema::hasColumn('subject_course_masters', 'co_cso_not_applicable_note')) {
                $subjectCourseMap->co_cso_not_applicable_note = $isNoCoCsoApplicable
                    ? (trim((string) $request->input('co_cso_not_applicable_note', '')) ?: null)
                    : null;
            }

            $subjectCourseMap->save();
        }

        return redirect()->back()->with('success', 'Course Master Updated');
    }

    function updateCourseSpecificObjective(Request $request, $id)
    {
        $cso = CoHasCso::findOrFail($id);

        $allowedShifts = $this->getShiftSlugs();
        $validated = $request->validate([
            'title' => 'required',
            'lectures_needed' => 'required',
            'shift' => ['nullable', Rule::in($allowedShifts)],
        ]);

        $normalizedTitle = $this->normalizeTitle($request->title);
        $defaultShift = $this->getDefaultShiftSlug();

        $hasDuplicate = CoHasCso::where('co_id', $cso->co_id)
            ->where('id', '!=', $cso->id)
            ->where(function ($q) use ($defaultShift) {
                $q->where('shift', $defaultShift)->orWhereNull('shift');
            })
            ->get(['title'])
            ->contains(fn($existing) => $this->normalizeTitle($existing->title) === $normalizedTitle);

        if ($hasDuplicate) {
            return redirect()->back()->with('error', 'Another unit with the same title already exists for this course and shift.');
        }

        $cso->title = $request->title;
        $cso->lectures_needed = $request->lectures_needed;
        $cso->shift = $defaultShift;
        $cso->save();

        return redirect()->back()->with('success', 'CSO updated successfully');
    }

    function deleteCourseSpecificObjective($id)
    {
        $cso = CoHasCso::findOrFail($id);
        $deptSubjectId = $this->currentDeptSubjectId();

        if (!$this->canDepartmentManageCso($cso, $deptSubjectId)) {
            return redirect()->back()->with('error', 'You are not allowed to delete this CSO.');
        }

        $linkedOtherSubject = SyllabusManager::where('cso_id', $id)
            ->where('subject_id', '!=', $deptSubjectId)
            ->exists();

        if ($linkedOtherSubject) {
            return redirect()->back()->with('error', 'This CSO is linked to another department and cannot be deleted here.');
        }

        // Block deletion if any syllabus entry using this CSO has attendance or timetable
        $syllabusRecords = SyllabusManager::where('cso_id', $id)->get();
        foreach ($syllabusRecords as $syllabus) {
            $coId    = $syllabus->co_id;
            $batchId = $syllabus->batch_id;

            $hasAttendance = StudentAttendance::where('course_id', $coId)->exists();
            $hasTimetable  = SubjectHasRoutine::where('subject_course_id', $coId)
                ->where('batch_id', $batchId)
                ->exists();

            if ($hasAttendance || $hasTimetable) {
                $reasons = [];
                if ($hasAttendance) $reasons[] = 'attendance records exist for this course';
                if ($hasTimetable)  $reasons[] = 'a faculty timetable is assigned to this course';
                return redirect()->back()->with(
                    'error',
                    'Cannot delete this CSO — ' . implode(' and ', $reasons) . '. Please clear those first.'
                );
            }
        }

        $cso->delete();
        return redirect()->back()->with('success', 'CSO deleted successfully');
    }

    function deleteCsoSubunit($id)
    {
        $subunit = CsoSubunit::findOrFail($id);
        $cso = CoHasCso::find($subunit->cso_id);
        $deptSubjectId = $this->currentDeptSubjectId();

        if (!$cso || !$this->canDepartmentManageCso($cso, $deptSubjectId)) {
            return redirect()->back()->with('error', 'You are not allowed to delete this subunit.');
        }

        $linkedOtherSubject = SyllabusSubunit::where('unit_id', $id)
            ->whereHas('syllabusManager', function ($query) use ($deptSubjectId) {
                $query->where('subject_id', '!=', $deptSubjectId);
            })
            ->exists();

        if ($linkedOtherSubject) {
            return redirect()->back()->with('error', 'This subunit is linked to another department and cannot be deleted here.');
        }

        // Block deletion if any syllabus subunit uses this and the course has attendance or timetable
        $syllabusSubunits = SyllabusSubunit::with('syllabusManager')
            ->where('unit_id', $id)
            ->get();

        foreach ($syllabusSubunits as $syllabusSubunit) {
            $syllabusManager = $syllabusSubunit->syllabusManager;
            if ($syllabusManager) {
                $coId    = $syllabusManager->co_id;
                $batchId = $syllabusManager->batch_id;

                $hasAttendance = StudentAttendance::where('course_id', $coId)->exists();
                $hasTimetable  = SubjectHasRoutine::where('subject_course_id', $coId)
                    ->where('batch_id', $batchId)
                    ->exists();

                if ($hasAttendance || $hasTimetable) {
                    $reasons = [];
                    if ($hasAttendance) $reasons[] = 'attendance records exist for this course';
                    if ($hasTimetable)  $reasons[] = 'a faculty timetable is assigned to this course';
                    return redirect()->back()->with(
                        'error',
                        'Cannot delete this subunit — ' . implode(' and ', $reasons) . '. Please clear those first.'
                    );
                }
            }
        }

        $subunit->delete();
        //delete taxanomy links
        SubunitHasRbt::where('subunit_id', $id)->delete();

        return redirect()->back()->with('success', 'Subunit deleted successfully');
    }

    function addCsoSubunit(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'cso_id' => 'required',
            'taxonomy' => 'required|array|min:1',
        ]);
        $taxonomy = $request->taxonomy;

        $rec = new CsoSubunit();
        $rec->cso_id = $request->cso_id;
        $rec->title = $request->title; // default value for subunit

        if (!empty($request->photo)) {
            $photo = $request->photo;
            $filePath = StaticController::s3_file_uploader($photo, 'cso-subunits');
            $rec->image_path = $filePath;
        }

        $rec->save();

        $subunitId = $rec->id;

        //Assign taxonomy levels
        for ($i = 0; $i < count($taxonomy); $i++) {
            SubunitHasRbt::create([
                'subunit_id' => $subunitId,
                'rbt_id' => $taxonomy[$i],
            ]);
        }

        return redirect()->back()->with('success', 'CSO Subunit Added Successfully');
    }

    function syllabusManager(Request $request)
    {

        $id = $request->id;
        $subject = Subject::find((int) $id);
        $batches = BatchMaster::all();
        $semesters = Semester::all();
        $cos =  SubjectCourseMaster::with([
            'courseMaster.coursetypemaster'
        ])
            ->where('subject_id', $id)
            ->get()
            ->unique('course_master_id')
            ->values();

        $courseApplicabilityMap = $cos->mapWithKeys(function ($item) {
            return [
                (int) ($item->course_master_id ?? 0) => [
                    'co_cso_not_applicable' => (bool) ($item->co_cso_not_applicable ?? false),
                    'co_cso_not_applicable_note' => trim((string) ($item->co_cso_not_applicable_note ?? '')),
                ],
            ];
        });

        $mappedCourseIds = $cos
            ->pluck('course_master_id')
            ->filter()
            ->map(fn($courseId) => (int) $courseId)
            ->unique()
            ->values();

        $data['id'] = $id;
        $data['slug'] = $request->slug;
        $subjectUsesShifts = $this->subjectUsesShifts($id);
        $shiftOptions = $this->getSubjectShiftOptions($subject);
        $allowedShiftSlugs = $shiftOptions->pluck('slug')->filter()->values()->all();
        $defaultShift = $this->getDefaultShiftSlug();
        $selectedProgramType = $this->resolveSyllabusProgramType($request, 'filter_program_type');

        $syllabusQuery = SyllabusManager::with([
            'subject',
            'batch',
            'semester',
            'courseobjective',
            'cso',
            'syllabusSubunits.csoSubunit.taxonomies.rbtmaster',
        ])->where('subject_id', $id);

        if (!empty($request->filter_batch)) {
            $syllabusQuery->where('batch_id', $request->filter_batch);
        }

        if ($subjectUsesShifts && !empty($request->filter_shift) && in_array($request->filter_shift, $allowedShiftSlugs, true)) {
            $syllabusQuery->where('shift', $request->filter_shift);
        }

        if ($subjectUsesShifts) {
            if (!empty($allowedShiftSlugs)) {
                $syllabusQuery->whereIn('shift', $allowedShiftSlugs);
            }
        } else {
            $syllabusQuery->where(function ($query) use ($defaultShift) {
                $query->where('shift', $defaultShift)->orWhereNull('shift');
            });
        }

        if (Schema::hasColumn('syllabus_managers', 'program_type') && !empty($request->filter_program_type)) {
            $syllabusQuery->where('program_type', $selectedProgramType);
        }

        if ($mappedCourseIds->isNotEmpty()) {
            $syllabusQuery->whereIn('co_id', $mappedCourseIds->all());
        } else {
            $syllabusQuery->whereRaw('1 = 0');
        }

        $syllabusData = $syllabusQuery->get();


        // Organize data: Batch -> Program Type -> Semester -> Course -> CSOs
        $organized = [];
        foreach ($syllabusData as $syllabus) {
            $batchName = $syllabus->batch->batch_name ?? 'Unknown Batch';
            $programLabel = strtoupper((string) ($syllabus->program_type ?? 'UG'));
            $programLabel = $programLabel === 'PG' ? 'PG' : 'UG';
            $semesterName = $syllabus->semester->title ?? 'Unknown Semester';
            $courseCode = $syllabus->courseobjective->course_code ?? 'N/A';
            $courseTitle = $syllabus->courseobjective->course_title ?? 'Unknown Course';
            $shiftLabel = Str::title($syllabus->shift ?? 'common');
            $courseKey = $courseCode . ' - ' . $courseTitle . ' [' . $shiftLabel . ']';

            if (!isset($organized[$batchName])) {
                $organized[$batchName] = [];
            }
            if (!isset($organized[$batchName][$programLabel])) {
                $organized[$batchName][$programLabel] = [];
            }
            if (!isset($organized[$batchName][$programLabel][$semesterName])) {
                $organized[$batchName][$programLabel][$semesterName] = [];
            }
            if (!isset($organized[$batchName][$programLabel][$semesterName][$courseKey])) {
                $organized[$batchName][$programLabel][$semesterName][$courseKey] = [
                    'course' => $syllabus->courseobjective,
                    'csos' => []
                ];
            }

            $organized[$batchName][$programLabel][$semesterName][$courseKey]['csos'][] = $syllabus;
        }

        $data['organized_syllabus'] = $organized;

        // Seat allocations keyed by "batch_id_semester_id_course_master_id" for quick lookup
        $seatAllocations = CourseSeatAllocation::where('subject_id', $id)
            ->get()
            ->keyBy(fn($a) => "{$a->batch_id}_{$a->semester_id}_{$a->course_master_id}");

        // Reference PDFs keyed the same way
        $syllabuspdfs = SyllabusPdfUpload::where('subject_id', $id)
            ->get()
            ->keyBy(fn($p) => "{$p->batch_id}_{$p->semester_id}_{$p->course_master_id}");

        return view('admin.subject.syllabus-manager', [
            'batches'        => $batches,
            'semesters'      => $semesters,
            'cos'            => $cos,
            'courseApplicabilityMap' => $courseApplicabilityMap,
            'data'           => $data,
            'shiftOptions'   => $shiftOptions,
            'selectedProgramType' => $selectedProgramType,
            'subjectUsesShifts' => $subjectUsesShifts,
            'seatAllocations' => $seatAllocations,
            'syllabuspdfs'   => $syllabuspdfs,
        ]);
    }

    function createSyllabus(Request $request)
    {
        $subject = Subject::find((int) $request->subject_id);
        $usesShifts = $this->subjectUsesShifts((int) $request->subject_id);
        $allowedShifts = $this->getSubjectAllowedShiftSlugs($subject);
        $defaultShift = $this->getDefaultShiftSlug();
        $request->validate([
            'subject_id' => 'required',
            'batch' => 'required',
            'semester' => 'required',
            'program_type' => 'required|in:UG,PG',
            'shift' => ['nullable', Rule::in($allowedShifts)],
            'co_id' => 'required',
            'cso_id' => 'nullable|array',
            'cso_id.*' => 'integer|exists:co_has_csos,id',
            'cso_subunit_map' => 'nullable|array',
            'status' => 'required|in:draft,published',
            'declare_no_co_cso' => 'nullable|in:0,1',
            'co_cso_not_applicable_note' => 'nullable|string|max:255',

        ]);

        $courseMasterId = (int) ($request->co_id ?? 0);
        $subjectCourseMap = SubjectCourseMaster::query()
            ->where('subject_id', (int) $request->subject_id)
            ->where('course_master_id', $courseMasterId)
            ->first();

        if (!$subjectCourseMap) {
            return redirect()->back()->with('error', 'Selected course is not mapped to this department.');
        }

        $declaredNoCoCsoInRequest = (int) $request->input('declare_no_co_cso', 0) === 1;
        $isAlreadyDeclaredNoCoCso = (bool) ($subjectCourseMap->co_cso_not_applicable ?? false);

        if ($declaredNoCoCsoInRequest) {
            $subjectCourseMap->co_cso_not_applicable = true;
            $subjectCourseMap->co_cso_not_applicable_note = trim((string) $request->input('co_cso_not_applicable_note', '')) ?: null;
            $subjectCourseMap->save();
            $isAlreadyDeclaredNoCoCso = true;
        }

        // Save syllabus only for one selected shift.
        $targetShifts = [$defaultShift];
        if ($usesShifts) {
            $selectedShift = (string) ($request->shift ?? '');
            if ($selectedShift !== '' && in_array($selectedShift, $allowedShifts, true)) {
                $targetShifts = [$selectedShift];
            } else {
                $targetShifts = [$defaultShift];
            }
        }

        $createdCount = 0;
        $updatedCount = 0;
        $status = $request->status ?? 'draft';
        $programType = $this->resolveSyllabusProgramType($request, 'program_type');
        $allowNoCoCsoFlow = $isAlreadyDeclaredNoCoCso;

        if (Schema::hasColumn('subject_has_syllabi', 'program_type')) {
            SubjectHasSyllabus::firstOrCreate([
                'subject_id' => $request->subject_id,
                'batch_id' => $request->batch,
                'semester_id' => $request->semester,
                'course_id' => $request->co_id,
                'program_type' => $programType,
            ]);
        }

        $selectedCsoIds = collect((array) $request->cso_id)
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $csoSubunitMap = collect((array) $request->cso_subunit_map)
            ->map(function ($subunitIds) {
                return collect((array) $subunitIds)
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values()
                    ->all();
            });

        $hasAtLeastOneSubunit = $selectedCsoIds
            ->contains(fn($csoId) => !empty($csoSubunitMap->get((string) $csoId, [])));

        if (!$allowNoCoCsoFlow && $selectedCsoIds->isEmpty()) {
            return redirect()->back()->with('error', 'Please select at least one CSO, or declare this course as CO/CSO not applicable.');
        }

        if (!$allowNoCoCsoFlow && !$hasAtLeastOneSubunit) {
            return redirect()->back()->with('error', 'Please select at least one subunit for the selected CSO(s).');
        }

        if ($allowNoCoCsoFlow) {
            foreach ($targetShifts as $shiftSlug) {
                $rec = SyllabusManager::updateOrCreate([
                    'subject_id' => $request->subject_id,
                    'batch_id' => $request->batch,
                    'semester_id' => $request->semester,
                    'shift' => $shiftSlug,
                    'program_type' => $programType,
                    'co_id' => $request->co_id,
                    'cso_id' => null,
                ], [
                    'status' => $status,
                ]);

                if ($rec->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }
            }

            if ($createdCount > 0 && $updatedCount > 0) {
                return redirect()->back()->with('success', 'Syllabus created and updated (CO/CSO not applicable) with status: ' . ucfirst($status));
            }

            return redirect()->back()->with('success', $createdCount > 0
                ? 'Syllabus created (CO/CSO not applicable) with status: ' . ucfirst($status)
                : 'Syllabus updated (CO/CSO not applicable) with status: ' . ucfirst($status));
        }

        foreach ($targetShifts as $shiftSlug) {
            foreach ($selectedCsoIds as $csoId) {
                $subunitIds = (array) $csoSubunitMap->get((string) $csoId, []);
                if (empty($subunitIds)) {
                    continue;
                }

                $rec = SyllabusManager::updateOrCreate([
                    'subject_id' => $request->subject_id,
                    'batch_id' => $request->batch,
                    'semester_id' => $request->semester,
                    'shift' => $shiftSlug,
                    'program_type' => $programType,
                    'co_id' => $request->co_id,
                    'cso_id' => $csoId,
                ], [
                    'status' => $status,
                ]);

                if ($rec->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }

                foreach ($subunitIds as $subunitId) {
                    SyllabusSubunit::firstOrCreate([
                        'syllabus_manager_id' => $rec->id,
                        'unit_id' => $subunitId,
                    ], [
                        'is_completed' => 0,
                    ]);
                }
            }
        }

        if ($createdCount > 0 && $updatedCount > 0) {
            return redirect()->back()->with('success', 'Syllabus created and updated with status: ' . ucfirst($status));
        }

        return redirect()->back()->with('success', $createdCount > 0
            ? 'Syllabus created with status: ' . ucfirst($status)
            : 'Syllabus updated with status: ' . ucfirst($status));
    }

    function deleteSyllabusSubunit($id)
    {
        $isJsonRequest = request()->expectsJson() || request()->ajax();
        $subunit = SyllabusSubunit::with('syllabusManager')->findOrFail($id);
        $syllabusManager = $subunit->syllabusManager;
        $deptSubjectId = $this->currentDeptSubjectId();

        if (!$syllabusManager || (int) $syllabusManager->subject_id !== (int) $deptSubjectId) {
            $message = 'You are not allowed to delete this syllabus subunit.';

            if ($isJsonRequest) {
                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], 403);
            }

            return redirect()->back()->with('error', $message);
        }

        if ($syllabusManager) {
            $coId    = $syllabusManager->co_id;
            $batchId = $syllabusManager->batch_id;

            $hasAttendance = StudentAttendance::where('course_id', $coId)->exists();
            $hasTimetable  = SubjectHasRoutine::where('subject_course_id', $coId)
                ->where('batch_id', $batchId)
                ->exists();

            if ($hasAttendance || $hasTimetable) {
                $reasons = [];
                if ($hasAttendance) $reasons[] = 'attendance records exist for this course';
                if ($hasTimetable)  $reasons[] = 'a faculty timetable is assigned to this course';
                $message = 'Cannot remove this subunit — ' . implode(' and ', $reasons) . '. Please clear those first.';

                if ($isJsonRequest) {
                    return response()->json([
                        'status' => false,
                        'message' => $message,
                    ], 422);
                }

                return redirect()->back()->with(
                    'error',
                    $message
                );
            }
        }

        $subunit->delete();

        if ($isJsonRequest) {
            return response()->json([
                'status' => true,
                'message' => 'Subunit removed',
            ]);
        }

        return redirect()->back()->with('success', 'Subunit removed');
    }

    function toggleSyllabusStatus(Request $request, $subjectId, $batchId, $semesterId, $coId)
    {
        $isJsonRequest = $request->expectsJson() || $request->ajax();
        $programType = $this->resolveSyllabusProgramType($request, 'program_type');

        $query = SyllabusManager::where('subject_id', $subjectId)
            ->where('batch_id', $batchId)
            ->where('semester_id', $semesterId)
            ->where('co_id', $coId);

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        if (Schema::hasColumn('syllabus_managers', 'program_type')) {
            $query->where('program_type', $programType);
        }

        $records = $query->get();

        if ($records->isEmpty()) {
            if ($isJsonRequest) {
                return response()->json([
                    'status' => false,
                    'message' => 'No syllabus records found to update status.',
                ], 404);
            }

            return redirect()->back()->with('error', 'No syllabus records found to update status.');
        }

        $currentStatus = strtolower((string) ($records->first()->status ?? 'draft'));
        $nextStatus = $currentStatus === 'published' ? 'draft' : 'published';

        SyllabusManager::whereIn('id', $records->pluck('id')->all())->update([
            'status' => $nextStatus,
        ]);

        if ($isJsonRequest) {
            return response()->json([
                'status' => true,
                'message' => 'Syllabus status changed to ' . ucfirst($nextStatus) . '.',
                'next_status' => $nextStatus,
            ]);
        }

        return redirect()->back()->with('success', 'Syllabus status changed to ' . ucfirst($nextStatus) . '.');
    }

    function deleteSyllabusCo(Request $request, $subjectId, $batchId, $semesterId, $coId)
    {
        $isJsonRequest = request()->expectsJson() || request()->ajax();
        $programType = $this->resolveSyllabusProgramType($request, 'program_type');
        $deptSubjectId = $this->currentDeptSubjectId();

        if ((int) $subjectId !== (int) $deptSubjectId) {
            $message = 'You are not allowed to delete this syllabus course.';

            if ($isJsonRequest) {
                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], 403);
            }

            return redirect()->back()->with('error', $message);
        }

        $hasAttendance = StudentAttendance::where('course_id', $coId)->exists();
        $hasTimetable  = SubjectHasRoutine::where('subject_course_id', $coId)
            ->where('batch_id', $batchId)
            ->exists();

        if ($hasAttendance || $hasTimetable) {
            $reasons = [];
            if ($hasAttendance) $reasons[] = 'attendance records exist for this course';
            if ($hasTimetable)  $reasons[] = 'a faculty timetable is assigned to this course';
            $message = 'Cannot remove this course from the syllabus — ' . implode(' and ', $reasons) . '. Please clear those first before making changes.';

            if ($isJsonRequest) {
                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->back()->with(
                'error',
                $message
            );
        }

        $records = SyllabusManager::where('subject_id', $subjectId)
            ->where('batch_id', $batchId)
            ->where('semester_id', $semesterId)
            ->where('co_id', $coId);

        if (Schema::hasColumn('syllabus_managers', 'program_type')) {
            $records->where('program_type', $programType);
        }

        $records = $records->get();

        foreach ($records as $record) {
            $record->syllabusSubunits()->delete();
            $record->delete();
        }

        if ($isJsonRequest) {
            return response()->json([
                'status' => true,
                'message' => 'Course and all its objectives removed from syllabus',
            ]);
        }

        return redirect()->back()->with('success', 'Course and all its objectives removed from syllabus');
    }

    function downloadSyllabusPdf(Request $request)
    {
        $id = $request->id;
        $subject = Subject::find($id);
        $subjectUsesShifts = $this->subjectUsesShifts((int) $id);
        $allowedShiftSlugs = $this->getSubjectAllowedShiftSlugs($subject);
        $selectedProgramType = $this->resolveSyllabusProgramType($request, 'filter_program_type');

        $syllabusQuery = SyllabusManager::with([
            'subject',
            'batch',
            'semester',
            'courseobjective',
            'cso',
            'syllabusSubunits.csoSubunit.taxomonylevel',
        ])->where('subject_id', $id);

        if (!empty($request->filter_batch)) {
            $syllabusQuery->where('batch_id', $request->filter_batch);
        }

        if ($subjectUsesShifts && !empty($request->filter_shift) && in_array($request->filter_shift, $allowedShiftSlugs, true)) {
            $syllabusQuery->where('shift', $request->filter_shift);
        }

        if ($subjectUsesShifts && !empty($allowedShiftSlugs)) {
            $syllabusQuery->whereIn('shift', $allowedShiftSlugs);
        } elseif (!$subjectUsesShifts) {
            $defaultShift = $this->getDefaultShiftSlug();
            $syllabusQuery->where(function ($query) use ($defaultShift) {
                $query->where('shift', $defaultShift)->orWhereNull('shift');
            });
        }

        if (Schema::hasColumn('syllabus_managers', 'program_type')) {
            $syllabusQuery->where('program_type', $selectedProgramType);
        }

        $syllabusData = $syllabusQuery->get();

        // Organize data: Batch -> Semester -> Course -> CSOs
        $organized = [];
        foreach ($syllabusData as $syllabus) {
            $batchName = $syllabus->batch->batch_name ?? 'Unknown Batch';
            $semesterName = $syllabus->semester->title ?? 'Unknown Semester';
            $courseCode = $syllabus->courseobjective->course_code ?? 'N/A';
            $courseTitle = $syllabus->courseobjective->course_title ?? 'Unknown Course';
            $shiftLabel = Str::title($syllabus->shift ?? 'common');
            $programLabel = strtoupper((string) ($syllabus->program_type ?? $selectedProgramType));
            $courseKey = $courseCode . ' - ' . $courseTitle . ' [' . $shiftLabel . ' | ' . $programLabel . ']';

            if (!isset($organized[$batchName])) {
                $organized[$batchName] = [];
            }
            if (!isset($organized[$batchName][$semesterName])) {
                $organized[$batchName][$semesterName] = [];
            }
            if (!isset($organized[$batchName][$semesterName][$courseKey])) {
                $organized[$batchName][$semesterName][$courseKey] = [
                    'course' => $syllabus->courseobjective,
                    'csos' => []
                ];
            }

            $organized[$batchName][$semesterName][$courseKey]['csos'][] = $syllabus;
        }

        $data = [
            'subject' => $subject,
            'organized_syllabus' => $organized,
            'selected_program_type' => $selectedProgramType,
            'slug' => $request->slug ?? $subject->slug
        ];

        $pdf = Pdf::loadView('admin.subject.syllabus-pdf', $data);
        return $pdf->download('syllabus-' . $subject->slug . '-' . date('Y-m-d') . '.pdf');
    }

    function showStudentList(Request $request)
    {
        $programId = $request->program_id;
        $slug = $request->slug;
        $batchId = $request->batch_id;
        $program = StudentProgram::findOrFail($programId);
        $students = StudentMaster::with(['batchmaster', 'campusmaster'])
            ->where('new_program_id', $programId)
            ->where('batch', $batchId)
            ->get();
        return view('admin.subject.student-list', [
            'students' => $students,
            'program' => $program,
            'slug' => $slug
        ]);
    }

    function integratedProgramStudentMappings(int $combinationId)
    {
        $userId = Auth::id();
        $subjectId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');

        $combination = SubjectHasStudentProgam::with([
            'studentprograminfo:id,code,name',
            'batchmaster:id,batch_name',
            'subjectmaster:id,title,slug',
        ])->find($combinationId);

        if (!$combination) {
            return redirect()->route('department.dashboard')->with('error', 'Program combination not found.');
        }

        if ((int) ($combination->subject_id ?? 0) !== (int) $subjectId) {
            return redirect()->route('department.dashboard')->with('error', 'You are not authorized to view this mapping.');
        }

        if (!Schema::hasTable('integrated_program_sublayer_settings')) {
            return redirect()->route('department.dashboard')->with('error', 'Integrated sublayer settings table not found. Please run migrations first.');
        }

        $integratedProgramId = (int) ($combination->student_program_id ?? 0);
        $isIntegratedProgram = DB::table('integrated_program_sublayer_settings')
            ->where('student_program_id', $integratedProgramId)
            ->where('is_active', 1)
            ->exists();

        if (!$isIntegratedProgram) {
            return redirect()->route('department.dashboard')->with('error', 'Selected program is not configured as an active integrated program.');
        }

        $hasIsDeletedColumn = Schema::hasColumn('student_masters', 'is_deleted');
        $hasIsLeftColumn = Schema::hasColumn('student_masters', 'is_left');
        $hasOriginProgramColumn = Schema::hasColumn('student_masters', 'integrated_origin_program_id');
        $hasShiftedAtColumn = Schema::hasColumn('student_masters', 'integrated_shifted_at');

        $students = StudentMaster::query()
            ->with('stdprogramenrolled:id,code,name')
            ->where('batch', (int) ($combination->batch_id ?? 0))
            ->where(function ($query) use ($integratedProgramId, $hasOriginProgramColumn) {
                $query->where('new_program_id', $integratedProgramId);
                if ($hasOriginProgramColumn) {
                    $query->orWhere('integrated_origin_program_id', $integratedProgramId);
                }
            })
            ->when($hasIsDeletedColumn, fn($query) => $query->where('is_deleted', 0))
            ->when($hasIsLeftColumn, fn($query) => $query->where('is_left', 0))
            ->orderBy('roll_no')
            ->orderBy('id')
            ->get([
                'id',
                'roll_no',
                'register_no',
                'first_name',
                'last_name',
                'new_program_id',
                'batch',
                'integrated_origin_program_id',
                'integrated_shifted_at',
            ]);

        $latestShiftByStudent = collect();
        if (Schema::hasTable('integrated_program_student_shifts')) {
            $latestShiftByStudent = DB::table('integrated_program_student_shifts')
                ->where('batch_id', (int) ($combination->batch_id ?? 0))
                ->where('from_program_id', $integratedProgramId)
                ->orderByDesc('id')
                ->get([
                    'id',
                    'student_id',
                    'to_program_id',
                    'to_combination_id',
                    'remarks',
                    'created_at',
                ])
                ->unique('student_id')
                ->keyBy('student_id');
        }

        $toProgramIds = $latestShiftByStudent
            ->pluck('to_program_id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $programLabelMap = StudentProgram::query()
            ->whereIn('id', $toProgramIds->all())
            ->get(['id', 'code', 'name'])
            ->mapWithKeys(function ($program) {
                $code = trim((string) ($program->code ?? ''));
                $name = trim((string) ($program->name ?? ''));
                $label = trim(($code !== '' ? $code . ' - ' : '') . $name);
                return [(int) $program->id => $label !== '' ? $label : ('Program #' . (int) $program->id)];
            });

        $toCombinationIds = $latestShiftByStudent
            ->pluck('to_combination_id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $combinationLabelMap = SubjectHasStudentProgam::with('subjectmaster:id,code,title')
            ->whereIn('id', $toCombinationIds->all())
            ->get(['id', 'subject_id'])
            ->mapWithKeys(function ($item) {
                $subjectCode = trim((string) (optional($item->subjectmaster)->code ?? ''));
                $subjectTitle = trim((string) (optional($item->subjectmaster)->title ?? ''));
                $subjectLabel = trim(($subjectCode !== '' ? $subjectCode . ' - ' : '') . $subjectTitle);
                return [(int) $item->id => $subjectLabel !== '' ? $subjectLabel : ('Combination #' . (int) $item->id)];
            });

        $rows = $students->map(function ($student) use (
            $latestShiftByStudent,
            $programLabelMap,
            $combinationLabelMap,
            $integratedProgramId,
            $hasShiftedAtColumn
        ) {
            $studentId = (int) ($student->id ?? 0);
            $latestShift = $latestShiftByStudent->get($studentId);

            $mappedProgramId = $latestShift ? (int) ($latestShift->to_program_id ?? 0) : 0;
            if ($mappedProgramId <= 0 && (int) ($student->new_program_id ?? 0) !== $integratedProgramId) {
                $mappedProgramId = (int) ($student->new_program_id ?? 0);
            }

            $mappedProgramName = $programLabelMap[$mappedProgramId] ?? null;
            if (!$mappedProgramName && $mappedProgramId > 0 && (int) ($student->new_program_id ?? 0) === $mappedProgramId && $student->stdprogramenrolled) {
                $code = trim((string) ($student->stdprogramenrolled->code ?? ''));
                $name = trim((string) ($student->stdprogramenrolled->name ?? ''));
                $mappedProgramName = trim(($code !== '' ? $code . ' - ' : '') . $name);
            }

            $mappedCombinationName = null;
            if ($latestShift && !empty($latestShift->to_combination_id)) {
                $mappedCombinationName = $combinationLabelMap[(int) $latestShift->to_combination_id] ?? ('Combination #' . (int) $latestShift->to_combination_id);
            }

            $studentName = trim(((string) ($student->first_name ?? '')) . ' ' . ((string) ($student->last_name ?? '')));

            return [
                'student_id' => $studentId,
                'roll_no' => (string) ($student->roll_no ?? '-'),
                'register_no' => (string) ($student->register_no ?? '-'),
                'student_name' => $studentName !== '' ? $studentName : '-',
                'mapped_program' => $mappedProgramName,
                'mapped_combination' => $mappedCombinationName,
                'mapped_on' => $latestShift->created_at ?? ($hasShiftedAtColumn ? ($student->integrated_shifted_at ?? null) : null),
                'remarks' => $latestShift->remarks ?? null,
                'status' => $mappedProgramId > 0 ? 'Mapped' : 'Not Mapped',
            ];
        })->values();

        return view('admin.subject.integrated-program-student-mapping', [
            'combination' => $combination,
            'rows' => $rows,
        ]);
    }

    function allStudents(Request $request)
    {
        $userId    = Auth::user()->id;
        $subjectId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
        $subject   = Subject::find($subjectId);

        if (!$subject) {
            return redirect()->route('department.dashboard')->with('info', 'Subject not found.');
        }

        $batches = BatchMaster::all();

        $query = StudentMaster::where('is_left', '0')->where('is_deleted', '0')
            ->with(['batchmaster', 'campusmaster', 'stdprogramenrolled']);

        if ($request->filled('batch_id')) {
            $query->where('batch', $request->batch_id);
        }

        $students = $query->orderby('id', 'desc')->get();

        return view('admin.subject.all-students', [
            'students'    => $students,
            'subject'     => $subject,
            'batches'     => $batches,
            'activeBatch' => $request->batch_id,
        ]);
    }

    function studentProfile(Request $request)
    {
        $id     = $request->id;
        $rollno = $request->rollno;

        $data = StudentMaster::where('id', $id)->where('roll_no', $rollno)->with([
            'religionmaster:id,name',
            'deptmaster:id,department_code,name',
            'campusmaster:id,slug,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'stdprogramenrolled',
            'feepayment.feepaymentinfo:id,quarter_title',
            'feepayment.gatewaytype',
            'academicpathway',
            'degreetrack',
            'singleselection',
            'subjectmaster',
        ])->firstOrFail();

        $studentId = $data->id;

        $studentCourses = StudentCourseInfo::with([
            'coursemaster.semestermaster:id,title',
            'coursemaster.coursetypemaster:id,title,description',
        ])
            ->where('student_id', $studentId)
            ->orderByDesc('id')
            ->get()
            ->unique(fn($c) => ($c->semester ?? $c->coursemaster?->semester_id ?? 'na') . '_' . $c->course_id)
            ->values();

        $semesterMap = Semester::pluck('title', 'id')->toArray();

        $coursesBySemester = $studentCourses
            ->sortBy(fn($c) => $c->semester ?? $c->coursemaster?->semester_id ?? 999)
            ->groupBy(function ($c) use ($semesterMap) {
                $semId = $c->semester ?? $c->coursemaster?->semester_id;
                return $semesterMap[$semId] ?? ('Semester ' . ($semId ?? '?'));
            });

        $faSegregatedMarks = CiaMark::where('STUDENT_ID', $studentId)->with([
            'studentcourseinfo.coursemaster:id,course_title,course_code,semester_id',
            'groupinfo.grouptype:id,name',
        ])->get()->groupBy(fn($c) => $c->SEMESTER_ID);

        $interMarkedCourseIds = InterMark::where('student_id', $studentId)->pluck('course_id')->unique()->toArray();
        $ciaMarkedCourseIds   = CiaMark::where('STUDENT_ID', $studentId)->pluck('COURSE_ID')->unique()->toArray();
        $saMarkedCourseIds    = DB::table('exam_marks_entries')->where('erp_student_id', $studentId)->pluck('erp_subject_id')->unique()->toArray();
        $lockedCourseIds      = array_unique(array_merge($interMarkedCourseIds, $ciaMarkedCourseIds, $saMarkedCourseIds));

        $enrolledCourseIds = $studentCourses->pluck('course_id')->toArray();
        $availableCourses = ProgramCourseMaster::where('is_deleted', 0)
            ->whereNotIn('id', $enrolledCourseIds)
            ->with('semestermaster:id,title', 'coursetypemaster:id,title')
            ->orderBy('semester_id')
            ->orderBy('course_title')
            ->get()
            ->groupBy(fn($c) => $c->semester_id);

        $availableSemesters = Semester::orderBy('id')->get();

        $deliveryContext = $this->resolveStudentDeliveryContext($data, $studentCourses);
        $timetable       = StudentTimetableService::generate($studentId);
        $timetableByDay  = $timetable->groupBy(fn($r) => $r['weekday'] ?? 'Unknown');

        $attendanceRaw = StudentAttendance::where('student_id', $studentId)
            ->with('courseinfo:id,course_title,course_code')
            ->get()
            ->groupBy('course_id');

        $attendanceSummary = $attendanceRaw->map(function ($records) {
            $total   = $records->count();
            $present = $records->where('status', 'present')->count();
            return [
                'course'     => $records->first()->courseinfo,
                'total'      => $total,
                'present'    => $present,
                'absent'     => $total - $present,
                'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ];
        })->values();

        $internalMarks = InterMark::where('student_id', $studentId)
            ->with(['course:id,course_title,course_code', 'semester:id,title'])
            ->where('is_deleted', 0)
            ->orderBy('semester')
            ->get();

        $faMarksByCourseSemester = $internalMarks
            ->sortByDesc('id')
            ->groupBy(fn($m) => (string) $m->semester . '_' . (string) $m->course_id)
            ->map(fn($rows) => $rows->first());

        $saMarksByCourseSemester = DB::table('exam_marks_entries as eme')
            ->join('exam_sessions as es', 'es.id', '=', 'eme.exam_session_id')
            ->where('eme.erp_student_id', $studentId)
            ->select('eme.erp_subject_id as course_id', 'es.semester as semester', DB::raw('MAX(eme.marks) as sa_marks'))
            ->groupBy('eme.erp_subject_id', 'es.semester')
            ->get()
            ->keyBy(fn($m) => (string) $m->semester . '_' . (string) $m->course_id);

        $ciaMarksBySemester = $studentCourses
            ->groupBy(fn($c) => (string) ($c->semester ?? $c->coursemaster?->semester_id ?? 'Unknown'))
            ->map(function ($courses, $semester) use ($faMarksByCourseSemester, $saMarksByCourseSemester, $semesterMap) {
                $rows = $courses
                    ->sortBy(fn($c) => $c->coursemaster?->course_code ?? 'ZZZ')
                    ->map(function ($course) use ($semester, $faMarksByCourseSemester, $saMarksByCourseSemester) {
                        $key = (string) $semester . '_' . (string) $course->course_id;
                        $fa  = $faMarksByCourseSemester->get($key);
                        $sa  = $saMarksByCourseSemester->get($key);
                        return [
                            'course'   => $course->coursemaster,
                            'fa_marks' => $fa?->internal_mark,
                            'sa_marks' => $sa?->sa_marks,
                            'semester' => $semester,
                        ];
                    })
                    ->values();
                return [
                    'label' => $semesterMap[(int) $semester] ?? ('Semester ' . $semester),
                    'rows'  => $rows,
                ];
            })
            ->values();

        $examStudent = ExamStudent::where('erp_student_id', $studentId)->first();
        $examResults = collect();
        if ($examStudent) {
            $examResults = Result::where('exam_student_id', $examStudent->id)
                ->where('is_published', true)
                ->with(['examSession', 'resultSubjects'])
                ->orderByDesc('created_at')
                ->get();
        }

        $student360Profile = null;
        try {
            $student360Profile = app(Student360Repository::class)->profile((int) $studentId);
        } catch (Throwable $e) {
            $student360Profile = null;
        }

        return view('principal.students.student-profile', [
            'data'                           => $data,
            'studentCourses'                 => $studentCourses,
            'coursesBySemester'              => $coursesBySemester,
            'lockedCourseIds'                => $lockedCourseIds,
            'availableCourses'               => $availableCourses,
            'availableSemesters'             => $availableSemesters,
            'timetableByDay'                 => $timetableByDay,
            'attendanceSummary'              => $attendanceSummary,
            'internalMarks'                  => $internalMarks,
            'ciaMarksBySemester'             => $ciaMarksBySemester,
            'faSegregatedMarks'              => $faSegregatedMarks,
            'examResults'                    => $examResults,
            'examStudent'                    => $examStudent,
            'batches'                        => BatchMaster::orderBy('batch_name')->get(),
            'departments'                    => DepartmentMaster::orderBy('name')->get(),
            'campuses'                       => Campus::orderBy('name')->get(),
            'religions'                      => ReligionMaster::orderBy('name')->get(),
            'nationalities'                  => NationalityMaster::orderBy('name')->get(),
            'bloodGroups'                    => BloodGroupMaster::orderBy('name')->get(),
            'studentMajorDeliveryType'       => $deliveryContext['studentMajorDeliveryType'],
            'studentApplicableDeliveryTypes' => $deliveryContext['studentApplicableDeliveryTypes'],
            'combo1Title'                    => $deliveryContext['combo1Title'],
            'combo2Title'                    => $deliveryContext['combo2Title'],
            'courseDeliveryMap'              => $deliveryContext['courseDeliveryMap'],
            'courseOfferingSubjectMap'       => $deliveryContext['courseOfferingSubjectMap'],
            'programOfferingSubjectTitle'    => $deliveryContext['programOfferingSubjectTitle'],
            'student360Profile'              => $student360Profile,
            'dept_view'                      => true,
        ]);
    }

    private function resolveStudentDeliveryContext(?StudentMaster $student, $studentCourses): array
    {
        $programCombination = null;
        if ($student && !empty($student->new_program_id) && !empty($student->batch)) {
            $programCombination = SubjectHasStudentProgam::with([
                'subjectmaster:id,title,code',
                'combomap.combo1:id,title,code',
                'combomap.combo2:id,title,code',
            ])
                ->where('student_program_id', (int) $student->new_program_id)
                ->where('batch_id', (int) $student->batch)
                ->orderBy('id')
                ->first();
        }

        $combo1Id = (int) ($programCombination?->combomap?->combo_id_1 ?? 0);
        if ($combo1Id <= 0) {
            $combo1Id = (int) ($programCombination?->subject_id ?? 0);
        }
        $combo2Id        = (int) ($programCombination?->combomap?->combo_id_2 ?? 0);
        $selectedComboId = (int) ($student->selected_combo_id ?? 0);

        $studentMajorDeliveryType = null;
        if ($selectedComboId > 0) {
            if ($selectedComboId === $combo1Id) {
                $studentMajorDeliveryType = ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
            } elseif ($combo2Id > 0 && $selectedComboId === $combo2Id) {
                $studentMajorDeliveryType = ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2;
            }
        } elseif ($combo1Id > 0 && $combo2Id <= 0) {
            $studentMajorDeliveryType = ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
        }

        $studentApplicableDeliveryTypes = collect([
            $studentMajorDeliveryType,
            ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON,
            ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE,
        ])->filter()->unique()->values();

        $courseDeliveryMap        = [];
        $courseOfferingSubjectMap = [];
        $programType = strtoupper(trim((string) ($programCombination?->program_type ?? '')));

        if ($programCombination && $studentCourses) {
            $courseIds = collect($studentCourses)
                ->pluck('course_id')
                ->map(fn($id) => (int) $id)
                ->filter()->unique()->values()->all();

            $semesterIds = collect($studentCourses)
                ->map(fn($course) => (int) ($course->semester ?? $course->coursemaster?->semester_id ?? 0))
                ->filter(fn($id) => $id > 0)->unique()->values()->all();

            if (!empty($courseIds)) {
                $deliveryRowsQuery = ProgramWiseSemesterCourse::where('program_combo_refid', (int) $programCombination->id)
                    ->where('batch', (int) $student->batch)
                    ->whereIn('course_id', $courseIds);

                $pathwayId     = (int) ($student->academic_pathway_id ?? 0);
                $degreeTrackId = (int) ($student->degree_track_id ?? 0);

                if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'academic_pathway_id')) {
                    $pathwayId > 0
                        ? $deliveryRowsQuery->where('academic_pathway_id', $pathwayId)
                        : $deliveryRowsQuery->whereNull('academic_pathway_id');
                }

                if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'degree_track_id')) {
                    $degreeTrackId > 0
                        ? $deliveryRowsQuery->where('degree_track_id', $degreeTrackId)
                        : $deliveryRowsQuery->whereNull('degree_track_id');
                }

                foreach ($deliveryRowsQuery->get(['semester', 'course_id', 'delivery_category']) as $row) {
                    $key = (string) ((int) $row->semester) . '_' . (string) ((int) $row->course_id);
                    if (!empty($row->delivery_category)) {
                        $courseDeliveryMap[$key] = (string) $row->delivery_category;
                    }
                }
            }

            if (!empty($courseIds) && !empty($semesterIds)) {
                $syllabusQuery = SyllabusManager::with('subject:id,title,code')
                    ->where('batch_id', (int) $student->batch)
                    ->whereIn('co_id', $courseIds)
                    ->whereIn('semester_id', $semesterIds);

                if (Schema::hasColumn('syllabus_managers', 'status')) {
                    $syllabusQuery->where('status', 'published');
                }
                if ($programType !== '' && Schema::hasColumn('syllabus_managers', 'program_type')) {
                    $syllabusQuery->whereRaw("UPPER(TRIM(COALESCE(program_type, ''))) = ?", [$programType]);
                }

                foreach ($syllabusQuery->get(['semester_id', 'co_id', 'subject_id']) as $row) {
                    $key          = (string) ((int) $row->semester_id) . '_' . (string) ((int) $row->co_id);
                    $subjectTitle = trim((string) ($row->subject?->title ?? ''));
                    if ($subjectTitle === '') {
                        continue;
                    }
                    $courseOfferingSubjectMap[$key]   = $courseOfferingSubjectMap[$key] ?? [];
                    if (!in_array($subjectTitle, $courseOfferingSubjectMap[$key], true)) {
                        $courseOfferingSubjectMap[$key][] = $subjectTitle;
                    }
                }
                foreach ($courseOfferingSubjectMap as $key => $subjects) {
                    $courseOfferingSubjectMap[$key] = implode(' / ', $subjects);
                }
            }
        }

        return [
            'studentMajorDeliveryType'       => $studentMajorDeliveryType,
            'studentApplicableDeliveryTypes' => $studentApplicableDeliveryTypes,
            'combo1Title'                    => (string) ($programCombination?->combomap?->combo1?->title ?? ''),
            'combo2Title'                    => (string) ($programCombination?->combomap?->combo2?->title ?? ''),
            'courseDeliveryMap'              => $courseDeliveryMap,
            'courseOfferingSubjectMap'       => $courseOfferingSubjectMap,
            'programOfferingSubjectTitle'    => (string) ($programCombination?->subjectmaster?->title ?? ''),
        ];
    }

    function deptFacultyList(Request $request, $subjectId, $slug)
    {
        $faculties = SubjectFacultyMaster::with('faculty')->where('subject_id', $subjectId)->get();
        $subject = Subject::find($subjectId);
        $selectedBatch = (int) $request->query('batch', 0);
        $hasTeachingAllocationLink = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

        $facultyIds = $faculties
            ->pluck('faculty_id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $facultyTimetables = collect();
        if ($facultyIds->isNotEmpty()) {
            $routineQuery = SubjectHasRoutine::query()
                ->whereHas('syllabus', function ($query) use ($subjectId) {
                    $query->where('subject_id', $subjectId);
                })
                ->where(function ($query) use ($facultyIds, $hasTeachingAllocationLink) {
                    $query->whereIn('faculty_id', $facultyIds)
                        ->orWhereHas('teachingAssignment', function ($assignmentQuery) use ($facultyIds) {
                            $assignmentQuery->whereIn('faculty_id', $facultyIds)
                                ->orWhereHas('facultyAssignments', function ($facultyAssignmentQuery) use ($facultyIds) {
                                    $facultyAssignmentQuery->whereIn('faculty_id', $facultyIds);
                                })
                                ->orWhereHas('coFacultyMembers', function ($coFacultyQuery) use ($facultyIds) {
                                    $coFacultyQuery->whereIn('faculties.id', $facultyIds);
                                });
                        });

                    if ($hasTeachingAllocationLink) {
                        $query->orWhereHas('teachingAllocation', function ($assignmentQuery) use ($facultyIds) {
                            $assignmentQuery->whereIn('faculty_id', $facultyIds)
                                ->orWhereHas('facultyAssignments', function ($facultyAssignmentQuery) use ($facultyIds) {
                                    $facultyAssignmentQuery->whereIn('faculty_id', $facultyIds);
                                })
                                ->orWhereHas('coFacultyMembers', function ($coFacultyQuery) use ($facultyIds) {
                                    $coFacultyQuery->whereIn('faculties.id', $facultyIds);
                                });
                        });
                    }
                })
                ->with([
                    'weekdaymaster:id,title',
                    'hourmaster',
                    'teachingAssignment:id,course_id,faculty_id,delivery_type,allocation_group',
                    'teachingAssignment.course:id,course_code,course_title,course_type',
                    'teachingAssignment.course.coursetypemaster:id,title',
                    'teachingAssignment.coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                    'syllabus.subject:id,title',
                    'syllabus.batchmaster:id,batch_name',
                    'syllabus.semestermaster:id,title',
                    'lecturehallmaster:id,title',
                    'subjectCourse.courseMaster:id,course_title,course_code,course_type',
                    'subjectCourse.courseMaster.coursetypemaster:id,title',
                ]);

            if ($hasTeachingAllocationLink) {
                $routineQuery->with([
                    'teachingAllocation:id,course_id,faculty_id,delivery_type,allocation_group',
                    'teachingAllocation.course:id,course_code,course_title,course_type',
                    'teachingAllocation.course.coursetypemaster:id,title',
                    'teachingAllocation.coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                ]);
            }

            if ($selectedBatch > 0) {
                $routineQuery->where('batch_id', $selectedBatch);
            }

            $facultyTimetables = $routineQuery
                ->orderBy('faculty_id')
                ->orderBy('weekday_id')
                ->orderBy('hour_id')
                ->get()
                ->flatMap(function ($routine) use ($hasTeachingAllocationLink) {
                    $assignment = $routine->teachingAssignment;
                    if (!$assignment && $hasTeachingAllocationLink) {
                        $assignment = $routine->teachingAllocation;
                    }

                    $course = $routine->subjectCourse->courseMaster ?? optional($assignment)->course;

                    $assignedFacultyIds = [];
                    if ($assignment) {
                        $assignedFacultyIds = $assignment->allAssignedFacultyIds();
                    }

                    if (empty($assignedFacultyIds)) {
                        $directFacultyId = (int) ($routine->faculty_id ?? 0);
                        if ($directFacultyId > 0) {
                            $assignedFacultyIds = [$directFacultyId];
                        }
                    }

                    if (empty($assignedFacultyIds)) {
                        return collect();
                    }

                    $hourNo = (int) ($routine->hourmaster->hour_no ?? $routine->hour_id ?? 0);
                    $hourName = (string) ($routine->hourmaster->name ?? $routine->hourmaster->title ?? ('Hour ' . $hourNo));
                    $startTime = (string) ($routine->hourmaster->start_time ?? '');
                    $endTime = (string) ($routine->hourmaster->end_time ?? '');
                    $hourLabel = $hourName;
                    if ($startTime !== '' && $endTime !== '') {
                        $hourLabel .= ' (' . $startTime . ' - ' . $endTime . ')';
                    }

                    $courseCode = (string) ($course->course_code ?? '');
                    $courseTitle = (string) ($course->course_title ?? '-');

                    return collect($assignedFacultyIds)
                        ->map(fn($assignedFacultyId) => [
                            'faculty_id' => (int) $assignedFacultyId,
                            'weekday' => $routine->weekdaymaster->title ?? '-',
                            'hour' => $hourLabel,
                            'hour_sort' => $hourNo > 0 ? $hourNo : (int) ($routine->hour_id ?? 0),
                            'subject' => $routine->syllabus->subject->title ?? '-',
                            'batch' => $routine->syllabus->batchmaster->batch_name ?? '-',
                            'semester' => $routine->syllabus->semestermaster->title ?? '-',
                            'lecture_hall' => $routine->lecturehallmaster->title ?? '-',
                            'course' => trim($courseCode . ($courseCode !== '' ? ' - ' : '') . $courseTitle),
                            'course_type' => (string) ($course->coursetypemaster->title ?? '-'),
                            'shift' => ucfirst((string) ($routine->shift ?? 'common')),
                            'program_type' => strtoupper((string) ($routine->program_type ?? 'UG')) === 'PG' ? 'PG' : 'UG',
                        ]);
                })
                ->groupBy('faculty_id')
                ->map(fn($entries) => $entries->values());
        }

        return view('admin.department.faculty.index', [
            'data' => $faculties,
            'subject' => $subject,
            'batches' => BatchMaster::latest()->get(),
            'selectedBatch' => $selectedBatch > 0 ? $selectedBatch : null,
            'facultyTimetables' => $facultyTimetables,
        ]);
    }

    // ─── Dept Admin: Combo Master page (dedicated) ───────────────────────────

    function comboMaster(Request $request)
    {
        $userId    = Auth::user()->id;
        $subjectId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
        $subject   = Subject::find($subjectId);

        if (!$subject) {
            return redirect()->route('department.dashboard')->with('info', 'Subject not found.');
        }

        $batches  = BatchMaster::all();
        $campuses = Campus::all();
        $subjects = Subject::where('campus_id', $subject->campus_id)
            ->orderBy('title')
            ->get();

        $query = SubjectCombinationMaster::with(['batch', 'campus', 'mainSubject', 'comboSubject'])
            ->where('main_subject_id', $subjectId);

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        $grouped = $query->latest()
            ->get()
            ->groupBy(fn($r) => $r->batch_id . '-' . $r->campus_id . '-' . $r->main_subject_id);

        return view('admin.subject.combo-master', compact(
            'subject',
            'grouped',
            'batches',
            'campuses',
            'subjects'
        ));
    }

    // ─── Subject Combination Master ──────────────────────────────────────────

    function subjectCombinationMaster(Request $request)
    {
        // Group all rows by batch + campus + main_subject for cleaner display
        $rows = SubjectCombinationMaster::with(['batch', 'campus', 'mainSubject', 'comboSubject'])
            ->latest()
            ->get();

        // Group: [batch_id-campus_id-main_subject_id] => collection of rows
        $grouped = $rows->groupBy(function ($r) {
            return $r->batch_id . '-' . $r->campus_id . '-' . $r->main_subject_id;
        });

        $batches  = BatchMaster::all();
        $campuses = Campus::all();
        $subjects = Subject::orderBy('title')->get();

        return view('admin.master.subject-combination-master', compact('grouped', 'rows', 'batches', 'campuses', 'subjects'));
    }

    function storeSubjectCombination(Request $request)
    {
        $request->validate([
            'batch_id'          => 'required|exists:batch_masters,id',
            'main_subject_id'   => 'required|exists:subjects,id',
            'combo_subject_ids' => 'required|array|min:1',
            'combo_subject_ids.*' => 'exists:subjects,id',
        ]);

        $inserted = 0;
        $skipped  = 0;
        $mainSubjectRecord = Subject::find($request->main_subject_id);
        $campus_id = $mainSubjectRecord->campus_id;
        foreach ($request->combo_subject_ids as $comboId) {
            if ($comboId == $request->main_subject_id) {
                $skipped++;
                continue;
            }

            $exists = SubjectCombinationMaster::where([
                'batch_id'        => $request->batch_id,
                'campus_id'       => $campus_id,
                'main_subject_id' => $request->main_subject_id,
                'combo_subject_id' => $comboId,
            ])->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            SubjectCombinationMaster::create([
                'batch_id'        => $request->batch_id,
                'campus_id'       => $campus_id,
                'main_subject_id' => $request->main_subject_id,
                'combo_subject_id' => $comboId,
            ]);
            $inserted++;
        }

        $msg = "$inserted combination(s) added.";
        if ($skipped) $msg .= " $skipped skipped (duplicate or same subject).";
        return back()->with('success', $msg);
    }

    function deleteSubjectCombination($id)
    {
        SubjectCombinationMaster::findOrFail($id)->delete();
        return back()->with('success', 'Subject combination deleted.');
    }

    function getAdmissionCombination()
    {
        $data = Qs::getAdmissionCombination();
        $subjects = Subject::orderBy('title')->get();
        $batches = BatchMaster::all();
        $programs = StudentProgram::with('campusmaster')->orderBy('name')->get();

        return view('admin.itcell.admission-combination', [
            'data' => $data,
            'subjects' => $subjects,
            'batches' => $batches,
            'programs' => $programs
        ]);
    }

    function storeStdProgramComboMap(Request $request)
    {
        $request->validate([
            'student_program_id' => 'required|exists:student_programs,id',
            'combo_id_1' => 'required|exists:subjects,id',
            'combo_id_2' => 'required|exists:subjects,id',
        ]);

        StdProgComboMap::create([
            'student_program_id' => $request->student_program_id,
            'combo_id_1' => $request->combo_id_1,
            'combo_id_2' => $request->combo_id_2,
        ]);

        return back()->with('success', 'Student program combo map added.');
    }

    /**
     * Department Attendance Monitor - View all attendance taken by faculty
     */
    function attendanceMonitor(Request $request)
    {
        $userId = Auth::user()->id;
        $subjectId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
        $subject = Subject::find($subjectId);

        if (!$subject) {
            return redirect()->route('department.dashboard')->with('info', 'Subject not found.');
        }

        // Get all faculty IDs in this department
        $facultyIds = SubjectFacultyMaster::where('subject_id', $subjectId)
            ->pluck('faculty_id')
            ->toArray();

        // Build query for attendance
        $query = StudentAttendance::with([
            'student:id,first_name,last_name,roll_no,register_no',
            'courseinfo:id,course_name',
            'routine.subjectsyllabus.coursemasterrelation:id,course_name'
        ])
            ->whereIn('faculty_id', $facultyIds)
            ->orderBy('attendance_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('attendance_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('attendance_date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('attendance_date', '<=', $request->end_date);
        } else {
            // Default to current month if no filter
            $query->whereYear('attendance_date', now()->year)
                ->whereMonth('attendance_date', now()->month);
        }

        // Faculty filter
        if ($request->filled('faculty_id')) {
            $query->where('faculty_id', $request->faculty_id);
        }

        // Batch filter
        if ($request->filled('batch')) {
            $query->where('batch', $request->batch);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendanceRecords = $query->paginate(50);

        // Get weekly analysis for the current filter
        $startDate = $request->filled('start_date') ? $request->start_date : now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : now()->endOfMonth()->format('Y-m-d');

        $weeklyAnalysis = $this->getWeeklyAnalysis($facultyIds, $startDate, $endDate);

        // Summary statistics
        $totalRecords = StudentAttendance::whereIn('faculty_id', $facultyIds)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->count();

        $presentCount = StudentAttendance::whereIn('faculty_id', $facultyIds)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('status', 'present')
            ->count();

        $absentCount = StudentAttendance::whereIn('faculty_id', $facultyIds)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('status', 'absent')
            ->count();

        $lateCount = StudentAttendance::whereIn('faculty_id', $facultyIds)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('status', 'late')
            ->count();

        $stats = [
            'total' => $totalRecords,
            'present' => $presentCount,
            'absent' => $absentCount,
            'late' => $lateCount,
            'present_percentage' => $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 2) : 0,
        ];

        // Get faculty list for filter dropdown
        $faculties = Faculty::whereIn('id', $facultyIds)
            ->orderBy('FIRST_NAME')
            ->get(['id', 'FIRST_NAME', 'LAST_NAME', 'USER_CODE']);

        // Get batch list
        $batches = BatchMaster::all();

        return view('admin.department.attendance-monitor', compact(
            'attendanceRecords',
            'subject',
            'weeklyAnalysis',
            'stats',
            'faculties',
            'batches',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get weekly analysis of attendance (present/absent breakdown by week)
     */
    private function getWeeklyAnalysis($facultyIds, $startDate, $endDate)
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        $weeklyData = [];
        $currentStart = $start->copy()->startOfWeek();

        while ($currentStart <= $end) {
            $weekStart = $currentStart->copy();
            $weekEnd = $currentStart->copy()->endOfWeek();

            // Don't go beyond the end date
            if ($weekEnd > $end) {
                $weekEnd = $end->copy();
            }

            // Don't start before the start date
            $queryStart = $weekStart < $start ? $start->copy() : $weekStart;

            $weekLabel = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');

            $presentCount = StudentAttendance::whereIn('faculty_id', $facultyIds)
                ->whereBetween('attendance_date', [$queryStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                ->where('status', 'present')
                ->count();

            $absentCount = StudentAttendance::whereIn('faculty_id', $facultyIds)
                ->whereBetween('attendance_date', [$queryStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                ->where('status', 'absent')
                ->count();

            $weeklyData[] = [
                'week' => $weekLabel,
                'present' => $presentCount,
                'absent' => $absentCount,
                'total' => $presentCount + $absentCount,
            ];

            $currentStart->addWeek();
        }

        return $weeklyData;
    }

    private function getShiftSlugs(): array
    {
        $slugs = ShiftMaster::where('is_active', 1)
            ->orderBy('sort_order')
            ->pluck('slug')
            ->toArray();

        if (empty($slugs)) {
            return ['common'];
        }

        return $slugs;
    }

    private function getSubjectShiftOptions(?Subject $subject)
    {
        $defaultShift = $this->getDefaultShiftSlug();
        $query = ShiftMaster::where('is_active', 1)
            ->orderBy('sort_order');

        if ($subject && (int) ($subject->has_shift_delivery ?? 0) === 1) {
            $enabledShiftIds = $this->getSubjectEnabledShiftIds($subject);
            if (!empty($enabledShiftIds)) {
                $query->whereIn('id', $enabledShiftIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $query->where('slug', $defaultShift);
        }

        $options = $query->get(['id', 'title', 'slug']);
        if ($options->isEmpty()) {
            $options = ShiftMaster::where('is_active', 1)
                ->where('slug', $defaultShift)
                ->get(['id', 'title', 'slug']);
        }

        return $options;
    }

    private function getSubjectAllowedShiftSlugs(?Subject $subject): array
    {
        $defaultShift = $this->getDefaultShiftSlug();
        $slugs = $this->getSubjectShiftOptions($subject)
            ->pluck('slug')
            ->filter()
            ->map(fn($slug) => (string) $slug)
            ->unique()
            ->values()
            ->all();

        if (empty($slugs)) {
            return [$defaultShift];
        }

        return $slugs;
    }

    private function getSubjectEnabledShiftIds(?Subject $subject): array
    {
        if (!$subject) {
            return [];
        }

        $shiftIds = $subject->shift_ids;
        if (is_string($shiftIds)) {
            $decoded = json_decode($shiftIds, true);
            $shiftIds = is_array($decoded) ? $decoded : [];
        }

        return collect((array) $shiftIds)
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function resolveSyllabusProgramType(Request $request, string $key = 'program_type'): string
    {
        $value = strtoupper(trim((string) $request->input($key, 'UG')));
        return $value === 'PG' ? 'PG' : 'UG';
    }

    private function normalizeTitle(?string $value): string
    {
        $normalized = mb_strtolower((string) $value);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return trim((string) $normalized);
    }

    private function dedupeCsosByTitle($csos)
    {
        return collect($csos)
            ->groupBy(fn($cso) => $this->normalizeTitle($cso->title))
            ->map(function ($group) {
                $primary = $group->sortBy('id')->first();

                $mergedSubunits = $group
                    ->flatMap(fn($cso) => collect($cso->csosubunits ?? []))
                    ->groupBy(fn($subunit) => $this->normalizeTitle($subunit->title))
                    ->map(fn($items) => $items->sortBy('id')->first())
                    ->values();

                if ($primary) {
                    $primary->setRelation('csosubunits', $mergedSubunits);
                }

                return $primary;
            })
            ->filter()
            ->values();
    }

    private function isKnownShift(string $shift): bool
    {
        return ShiftMaster::where('slug', $shift)->exists();
    }

    private function getDefaultShiftSlug(): string
    {
        $common = ShiftMaster::where('slug', 'common')->value('slug');
        if (!empty($common)) {
            return $common;
        }

        $fallback = ShiftMaster::orderBy('sort_order')->value('slug');
        return $fallback ?: 'common';
    }


    function deleteCsoSubunitTaxonomy($id)
    {
        $taxonomy = SubunitHasRbt::findOrFail($id);
        $subunit = CsoSubunit::find($taxonomy->subunit_id);
        $deptSubjectId = $this->currentDeptSubjectId();
        $cso = $subunit ? CoHasCso::find($subunit->cso_id) : null;

        if (!$cso || !$this->canDepartmentManageCso($cso, $deptSubjectId)) {
            return redirect()->back()->with('error', 'You are not allowed to remove this taxonomy mapping.');
        }

        $taxonomy->delete();
        return redirect()->back()->with('success', 'Taxonomy level removed from subunit');
    }

    private function currentDeptSubjectId(): int
    {
        return (int) SubjectHasDeptAdmin::where('user_id', Auth::id())->value('subject_id');
    }

    private function canDepartmentManageCso(CoHasCso $cso, int $deptSubjectId): bool
    {
        if ($deptSubjectId <= 0) {
            return false;
        }

        return SubjectCourseMaster::where('course_master_id', (int) $cso->co_id)
            ->where('subject_id', $deptSubjectId)
            ->exists();
    }


    function updateCsoSubunit(Request $request, $id)
    {
        $subunit = CsoSubunit::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'taxonomy' => 'required|array|min:1',
        ]);

        $subunit->title = $request->title;

        if (!empty($request->photo)) {
            $photo = $request->photo;
            $filePath = StaticController::s3_file_uploader($photo, 'cso-subunits');
            $subunit->image_path = $filePath;
        }

        $subunit->save();

        // Update taxonomy levels
        $existingTaxonomyIds = SubunitHasRbt::where('subunit_id', $id)->pluck('rbt_id')->toArray();
        $newTaxonomyIds = $request->taxonomy;

        // Add new taxonomy levels
        foreach ($newTaxonomyIds as $rbtId) {
            if (!in_array($rbtId, $existingTaxonomyIds)) {
                SubunitHasRbt::create([
                    'subunit_id' => $id,
                    'rbt_id' => $rbtId,
                ]);
            }
        }

        // Remove old taxonomy levels that are not in the new list
        foreach ($existingTaxonomyIds as $existingId) {
            if (!in_array($existingId, $newTaxonomyIds)) {
                SubunitHasRbt::where('subunit_id', $id)->where('rbt_id', $existingId)->delete();
            }
        }

        return redirect()->back()->with('success', 'CSO Subunit updated successfully');
    }

    function toggleAdmissionFormVisibility($id)
    {
        $data = Subject::find($id);
        Subject::where('id', $id)->update([
            'display_in_admission_form' =>  $data->display_in_admission_form === 1 ? 0 : 1,
        ]);

        return redirect()->back()->with('success', 'Department Admission Form Visibility Updated');
    }

    function toggleSubjectShiftMode($id)
    {
        $data = Subject::find($id);
        $nextState = $data->has_shift_delivery === 1 ? 0 : 1;

        $payload = [
            'has_shift_delivery' => $nextState,
        ];

        if (Schema::hasColumn('subjects', 'shift_ids') && $nextState === 0) {
            $payload['shift_ids'] = null;
        }

        Subject::where('id', $id)->update($payload);

        return redirect()->back()->with('success', 'Subject shift mode updated');
    }

    function updateSubjectShifts(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $validated = $request->validate([
            'shift_ids' => 'nullable|array',
            'shift_ids.*' => 'integer|exists:shift_masters,id',
        ]);

        $shiftIds = collect($validated['shift_ids'] ?? [])
            ->map(fn($value) => (int) $value)
            ->filter()
            ->unique()
            ->values();

        $activeShiftIds = ShiftMaster::where('is_active', 1)
            ->whereIn('id', $shiftIds)
            ->pluck('id')
            ->map(fn($value) => (int) $value)
            ->values();

        $subject->shift_ids = $activeShiftIds->isNotEmpty() ? $activeShiftIds->all() : null;
        $subject->has_shift_delivery = $activeShiftIds->isNotEmpty() ? 1 : 0;
        $subject->save();

        return redirect()->back()->with('success', 'Enabled shifts updated successfully.');
    }

    function toggleTeachingAssignmentMultiPrimaryMode($id)
    {
        $subject = Subject::find($id);
        if (!$subject) {
            return redirect()->back()->with('error', 'Department not found.');
        }

        if (!Schema::hasColumn('subjects', 'allow_multi_primary_faculty')) {
            return redirect()->back()->with('error', 'Multi primary faculty setting is not available yet. Please run latest migrations.');
        }

        $subject->allow_multi_primary_faculty = (int) ($subject->allow_multi_primary_faculty ?? 0) === 1 ? 0 : 1;
        $subject->save();

        return redirect()->back()->with(
            'success',
            (int) $subject->allow_multi_primary_faculty === 1
                ? 'Multi primary faculty mode enabled for this department.'
                : 'Single primary faculty mode enabled for this department.'
        );
    }

    private function subjectUsesShifts(?int $subjectId): bool
    {
        if (empty($subjectId)) {
            return false;
        }

        return Subject::where('id', $subjectId)->value('has_shift_delivery') == 1;
    }

    private function subjectAllowsMultiPrimaryFaculty(?Subject $subject): bool
    {
        if (!$subject || !Schema::hasColumn('subjects', 'allow_multi_primary_faculty')) {
            return false;
        }

        return (int) ($subject->allow_multi_primary_faculty ?? 0) === 1;
    }

    private function getIntegratedSublayerContextForCombination(?SubjectHasStudentProgam $combination): array
    {
        if (!$combination || !Schema::hasTable('integrated_program_sublayer_settings')) {
            return [
                'is_integrated' => false,
                'setting' => null,
                'sublayer_combinations' => collect(),
            ];
        }

        $setting = DB::table('integrated_program_sublayer_settings')
            ->where('student_program_id', (int) ($combination->student_program_id ?? 0))
            ->where('is_active', 1)
            ->first();

        if (!$setting) {
            return [
                'is_integrated' => false,
                'setting' => null,
                'sublayer_combinations' => collect(),
            ];
        }

        $integratedProgramIds = DB::table('integrated_program_sublayer_settings')
            ->where('is_active', 1)
            ->pluck('student_program_id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $sublayerCombinations = SubjectHasStudentProgam::with([
            'studentprograminfo:id,code,name,program_type',
            'studentprograminfo.programtypemaster:id,name',
            'subjectmaster:id,code,title',
            'batchmaster:id,batch_name',
        ])
            ->where('batch_id', (int) ($combination->batch_id ?? 0))
            ->where('subject_id', (int) ($combination->subject_id ?? 0))
            ->where('id', '!=', (int) ($combination->id ?? 0))
            ->where('student_program_id', '!=', (int) ($combination->student_program_id ?? 0))
            ->when($integratedProgramIds->isNotEmpty(), fn($query) => $query->whereNotIn('student_program_id', $integratedProgramIds->all()))
            ->orderBy('student_program_id')
            ->get(['id', 'student_program_id', 'subject_id', 'batch_id', 'program_type']);

        return [
            'is_integrated' => true,
            'setting' => $setting,
            'sublayer_combinations' => $sublayerCombinations,
        ];
    }

    private function getCoursesBySemesterFromCombinationIds(array $combinationIds): \Illuminate\Support\Collection
    {
        $combinationIds = collect($combinationIds)
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        if ($combinationIds->isEmpty()) {
            return collect();
        }

        $curriculumTable = $this->getCurriculumEngineTable();
        $rowsQuery = ProgramWiseSemesterCourse::with([
            'semestermaster:id,title',
            'programinfo:id,course_code,course_title,course_type,department',
            'programinfo.coursetypemaster:id,title',
            'specializationmaster:id,name',
        ])->whereIn('program_combo_refid', $combinationIds->all())
            ->orderBy('semester')
            ->when(
                Schema::hasColumn($curriculumTable, 'display_order'),
                fn($query) => $query->orderBy('display_order')->orderBy('id'),
                fn($query) => $query->orderBy('id')
            );

        if (Schema::hasColumn($curriculumTable, 'is_active')) {
            $rowsQuery->where('is_active', 1);
        }

        return $rowsQuery->get()->groupBy('semester');
    }

    function curriculamBuilder($id, $code)
    {

        $data = SubjectHasStudentProgam::with([
            'studentprograminfo:id,code,name',
            'combomap.combo1',
            'combomap.combo2',
            'batchmaster:id,batch_name'

        ])->find($id);

        if (!$data) {
            return redirect()->back()->with('error', 'Program mapping not found.');
        }

        $integratedContext = $this->getIntegratedSublayerContextForCombination($data);
        $isIntegratedSublayerReadOnly = (bool) ($integratedContext['is_integrated'] ?? false);
        $integratedSublayerCombinations = collect($integratedContext['sublayer_combinations'] ?? collect())->values();
        $integratedSublayerPrograms = $integratedSublayerCombinations
            ->map(function ($combination) {
                $programCode = (string) (optional($combination->studentprograminfo)->code ?? '-');
                $programName = (string) (optional($combination->studentprograminfo)->name ?? '-');
                return trim(($programCode !== '' ? $programCode . ' - ' : '') . $programName);
            })
            ->filter()
            ->unique()
            ->values();

        $comboBoundary = $this->getProgrammeBoundarySubjectIds($data);

        $coursesBySemester = $isIntegratedSublayerReadOnly
            ? $this->getCoursesBySemesterFromCombinationIds($integratedSublayerCombinations->pluck('id')->all())
            : $this->getCoursesBySemesterFromCombinationIds([(int) $id]);

        $publishedCoursesBySemester = $this->getPublishedCoursesBySemesterForCombination($data);
        $selectedSemester = (int) request('semester');
        $generatedCourses = $selectedSemester > 0
            ? collect($publishedCoursesBySemester[(string) $selectedSemester] ?? [])->values()
            : collect();

        if ($isIntegratedSublayerReadOnly) {
            $generatedCourses = collect();
            $selectedSemester = 0;
        }

        $semesters = Semester::query()->orderBy('id')->get(['id', 'title']);
        $pathways = AcademicPathwayMaster::query()->orderBy('name')->get(['id', 'name']);
        $degreeTracks = DegreeTrackMaster::query()->orderBy('name')->get(['id', 'name']);

        $combo1DepartmentId = (int) ($comboBoundary['combo1'] ?? 0);
        $combo2DepartmentId = (int) ($comboBoundary['combo2'] ?? 0);
        $isSingleMajorCourse = $combo1DepartmentId > 0 && $combo1DepartmentId === $combo2DepartmentId;

        $combinationSpecializationIds = collect((array) ($data->specialization_ids ?? []))
            ->map(fn($value) => (int) $value)
            ->filter()
            ->unique()
            ->values();

        $availableSpecializations = collect();
        if ($isSingleMajorCourse) {
            $availableSpecializations = SpecializationMaster::query()
                ->where('subject_id', (int) ($data->subject_id ?? 0))
                ->where('is_active', 1)
                ->when($combinationSpecializationIds->isNotEmpty(), fn($query) => $query->whereIn('id', $combinationSpecializationIds->all()))
                ->orderBy('name')
                ->get(['id', 'name']);
        }


        return view('admin.subject.curriculam-builder', [
            'data' => $data,
            'comboBoundary' => $comboBoundary,
            'coursesBySemester' => $coursesBySemester,
            'publishedCoursesBySemester' => $publishedCoursesBySemester,
            'selectedSemester' => $selectedSemester,
            'generatedCourses' => $generatedCourses,
            'semesters' => $semesters,
            'pathways' => $pathways,
            'degreeTracks' => $degreeTracks,
            'availableSpecializations' => $availableSpecializations,
            'isIntegratedSublayerReadOnly' => $isIntegratedSublayerReadOnly,
            'integratedSublayerPrograms' => $integratedSublayerPrograms,

        ]);
    }

    function fetchComboCourses(Request $request)
    {
        $request->validate([
            'student_program_id' => 'required|integer',
            'semester' => 'required|integer|exists:semesters,id',
            'combo1' => 'nullable|integer|exists:subjects,id',
            'combo2' => 'nullable|integer|exists:subjects,id',
            'batch' => 'required|integer|exists:batch_masters,id'
        ]);

        $combinationQuery = SubjectHasStudentProgam::with([
            'combomap:id,student_program_id,combo_id_1,combo_id_2',
            'combomap.combo1:id,title',
            'combomap.combo2:id,title',
        ]);

        // Preferred path: incoming value is the combination row id.
        $combination = (clone $combinationQuery)->find((int) $request->student_program_id);

        // Backward compatibility: allow payload that sends student_program_id instead of combination id.
        if (!$combination) {
            $combination = (clone $combinationQuery)
                ->where('student_program_id', (int) $request->student_program_id)
                ->where('batch_id', (int) $request->batch)
                ->orderBy('id')
                ->first();
        }

        if (!$combination) {
            return response()->json([
                'status' => false,
                'message' => 'Program mapping not found.',
            ], 404);
        }

        $combo1SubjectId = (int) ($request->combo1 ?? optional($combination->combomap)->combo_id_1 ?? 0);
        $combo2SubjectId = (int) ($request->combo2 ?? optional($combination->combomap)->combo_id_2 ?? 0);
        $programType = $this->resolveCombinationProgramType($combination);
        $semester = $request->semester;
        $batch = $request->batch;

        $combo1OfferedCoursesQuery = SyllabusManager::with(['courseobjective.coursetypemaster', 'subject:id,title,code'])
            ->where('subject_id', $combo1SubjectId)->where('batch_id', $batch)->where('semester_id', $semester)
            ->where('status', 'published');

        if (Schema::hasColumn('syllabus_managers', 'program_type')) {
            $combo1OfferedCoursesQuery->whereRaw("UPPER(TRIM(COALESCE(program_type, ''))) = ?", [$programType]);
        }

        $combo1OfferedCourses = $combo1OfferedCoursesQuery->get();

        $combo2OfferedCoursesQuery = SyllabusManager::with(['courseobjective.coursetypemaster', 'subject:id,title,code'])
            ->where('subject_id', $combo2SubjectId)->where('batch_id', $batch)->where('semester_id', $semester)
            ->where('status', 'published');

        if (Schema::hasColumn('syllabus_managers', 'program_type')) {
            $combo2OfferedCoursesQuery->whereRaw("UPPER(TRIM(COALESCE(program_type, ''))) = ?", [$programType]);
        }

        $combo2OfferedCourses = $combo2OfferedCoursesQuery->get();

        $mapCourses = function ($syllabi, string $source) {
            return collect($syllabi)
                ->map(function ($syllabus) use ($source) {
                    $course = $syllabus->courseobjective;
                    if (!$course) {
                        return null;
                    }

                    $courseTypeTitle = strtoupper(trim((string) optional($course->coursetypemaster)->title));
                    $courseType = $courseTypeTitle !== '' ? $courseTypeTitle : 'NA';

                    // MAJ from combo1 should be COMBO1.
                    if ($courseTypeTitle === 'MAJ') {
                        $courseType = $source === 'combo1' ? ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1 : ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2;
                    }

                    $sourceSubjectId = (int) ($syllabus->subject_id ?? 0);
                    $sourceSubject = (string) (optional($syllabus->subject)->title ?? 'NA');
                    $sourceSubjectCode = (string) (optional($syllabus->subject)->code ?? '');

                    return [
                        'id' => (int) $course->id,
                        'course_code' => (string) ($course->course_code ?? ''),
                        'course_title' => (string) ($course->course_title ?? ''),
                        'course_type_title' => $courseTypeTitle !== '' ? $courseTypeTitle : 'NA',
                        'course_type' => $courseType,
                        'source_combo' => $source,
                        'source_subject_id' => $sourceSubjectId,
                        'source_subject' => $sourceSubject,
                        'source_subject_code' => $sourceSubjectCode,
                    ];
                })
                ->filter();
        };

        $combo1Courses = $mapCourses($combo1OfferedCourses, 'combo1');
        $combo2Courses = $mapCourses($combo2OfferedCourses, 'combo2');

        // Keep combo1 precedence when a course appears in both combos.
        $semesterCourses = $combo2Courses
            ->concat($combo1Courses)
            ->keyBy('id')
            ->values()
            ->sortBy(fn($course) => ($course['course_code'] ?? '') . ' ' . ($course['course_title'] ?? ''))
            ->values()
            ->all();

        return response()->json([
            'status' => true,
            'semester' => (int) $request->semester,
            'combo1' => $combo1SubjectId,
            'combo2' => $combo2SubjectId,
            'program_type' => $programType,
            'data' => $semesterCourses,
        ]);
    }

    function publishedSyllabusCoursesForCurriculum(Request $request, $id)
    {
        $combination = SubjectHasStudentProgam::with([
            'studentprograminfo:id,code,name',
            'combomap:id,student_program_id,combo_id_1,combo_id_2',
        ])->find($id);

        if (!$combination) {
            return response()->json([
                'status' => false,
                'message' => 'Program mapping not found.',
            ], 404);
        }

        $coursesBySemester = $this->getPublishedCoursesBySemesterForCombination($combination);

        if ($request->filled('semester')) {
            $semesterId = (string) ((int) $request->semester);
            return response()->json([
                'status' => true,
                'data' => $coursesBySemester[$semesterId] ?? [],
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $coursesBySemester,
        ]);
    }

    function storeProgramSemesterCoursesMapping(Request $request)
    {
        $isJsonRequest = $request->expectsJson() || $request->ajax();
        $respond = function (bool $status, string $message, int $httpCode = 200, array $extra = []) use ($isJsonRequest) {
            if ($isJsonRequest) {
                return response()->json(array_merge([
                    'status' => $status,
                    'message' => $message,
                ], $extra), $httpCode);
            }

            return redirect()->back()->with($status ? 'success' : 'error', $message);
        };

        // return $request->all();
        $request->validate([
            'semester' => 'required|integer|exists:semesters,id',
            'selected_courses' => 'nullable|array',
            'selected_courses.*' => 'integer|exists:program_course_masters,id',
            'course_type_map' => 'nullable|array',
            'delivery_category_map' => 'nullable|array',
            'course' => 'nullable|array',
            'course.*' => 'integer|exists:program_course_masters,id',
            'course_type' => 'nullable|in:AUTO,STUDENT_CHOICE,DEPARTMENT_CHOICE',
            'academic_pathway_id' => 'nullable|integer|exists:academic_pathway_masters,id',
            'degree_track_id' => 'nullable|integer|exists:degree_track_masters,id',
        ]);

        $refid = $request->id;
        $semester = (int) $request->semester;
        $batch = $request->batch;
        $academicPathwayId = $request->filled('academic_pathway_id') ? (int) $request->academic_pathway_id : null;
        $degreeTrackId = $request->filled('degree_track_id') ? (int) $request->degree_track_id : null;
        $curriculumTable = $this->getCurriculumEngineTable();
        $hasAcademicPathwayColumn = Schema::hasColumn($curriculumTable, 'academic_pathway_id');
        $hasDegreeTrackColumn = Schema::hasColumn($curriculumTable, 'degree_track_id');
        $hasOfferingDeptColumn = Schema::hasColumn($curriculumTable, 'offering_dept');
        $hasDeliveryCategoryColumn = Schema::hasColumn($curriculumTable, 'delivery_category');
        $hasSpecializationModeColumn = Schema::hasColumn($curriculumTable, 'specialization_mode');
        $hasSpecializationMasterColumn = Schema::hasColumn($curriculumTable, 'specialization_master_id');
        $hasSpecializationMasterIdsColumn = Schema::hasColumn($curriculumTable, 'specialization_master_ids');
        $hasDisplayOrderColumn = Schema::hasColumn($curriculumTable, 'display_order');
        $hasIsActiveColumn = Schema::hasColumn($curriculumTable, 'is_active');

        if (($academicPathwayId !== null && !$hasAcademicPathwayColumn) || ($degreeTrackId !== null && !$hasDegreeTrackColumn)) {
            return $respond(false, 'Pathway/Track columns are missing in curriculum table. Please run latest migrations and try again.', 422);
        }

        $targetAcademicPathwayIds = [$academicPathwayId];
        if ($hasAcademicPathwayColumn && $academicPathwayId === null && $degreeTrackId !== null) {
            $degreeTrackName = strtoupper(trim((string) DB::table('degree_track_masters')
                ->where('id', $degreeTrackId)
                ->value('name')));

            // "All Pathways + Regular" should create mappings for each explicit pathway only.
            if ($degreeTrackName === 'REGULAR') {
                $allPathwayIds = DB::table('academic_pathway_masters')
                    ->pluck('id')
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values()
                    ->all();

                $targetAcademicPathwayIds = !empty($allPathwayIds) ? $allPathwayIds : [null];
            }
        }

        $combination = SubjectHasStudentProgam::with([
            'batchmaster:id,batch_name',
            'studentprograminfo:id,code,name',
            'combomap:id,student_program_id,combo_id_1,combo_id_2',
        ])->find($refid);
        if (!$combination) {
            return $respond(false, 'Program mapping not found.', 404);
        }

        $integratedContext = $this->getIntegratedSublayerContextForCombination($combination);
        if ((bool) ($integratedContext['is_integrated'] ?? false)) {
            return $respond(false, 'Curriculum design is locked for integrated programs. Please use configured UG/PG sublayer programs.', 422);
        }

        $comboBoundary = $this->getProgrammeBoundarySubjectIds($combination);
        $isSingleMajor = (int) ($comboBoundary['combo1'] ?? 0) > 0
            && (int) ($comboBoundary['combo1'] ?? 0) === (int) ($comboBoundary['combo2'] ?? 0);

        if ($isSingleMajor && (!$hasSpecializationModeColumn || !$hasSpecializationMasterColumn || !$hasSpecializationMasterIdsColumn)) {
            return $respond(false, 'Specialization columns are missing in curriculum table. Please run latest migrations and try again.', 422);
        }

        $allowedSpecializationIds = collect((array) ($combination->specialization_ids ?? []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $eligibleCourseIds = $this->getEligiblePublishedCourseIdsForSemester($combination, (int) $semester);

        $typedSelections = collect((array) $request->selected_courses)
            ->map(fn($courseId) => (int) $courseId)
            ->unique()
            ->map(function ($courseId) use ($request, $isSingleMajor) {
                $rawType = data_get($request->input('course_type_map', []), (string) $courseId);
                $type = strtoupper((string) $rawType);
                $rawDeliveryCategory = data_get($request->input('delivery_category_map', []), (string) $courseId);
                $deliveryCategory = $this->normalizeDeliveryCategoryInput($rawDeliveryCategory);
                $specializationMode = 'COMMON';
                $specializationMasterId = null;
                $specializationMasterIds = [];

                if ($isSingleMajor) {
                    $rawSpecializationMode = strtoupper(trim((string) data_get($request->input('specialization_mode_map', []), (string) $courseId, 'COMMON')));
                    $specializationMode = $rawSpecializationMode === 'SPECIALIZATION' ? 'SPECIALIZATION' : 'COMMON';
                    $rawSpecializationIds = collect((array) data_get($request->input('specialization_ids_map', []), (string) $courseId, []))
                        ->map(fn($value) => (int) $value)
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    if ($specializationMode === 'SPECIALIZATION' && !empty($rawSpecializationIds)) {
                        $specializationMasterIds = $rawSpecializationIds;
                        $specializationMasterId = $specializationMasterIds[0] ?? null;
                    }
                }

                if (!in_array($type, [
                    ProgramWiseSemesterCourse::TYPE_AUTO,
                    ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE,
                    ProgramWiseSemesterCourse::TYPE_DEPARTMENT_CHOICE,
                ], true)) {
                    $type = ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE;
                }

                return [
                    'course_id' => $courseId,
                    'course_type' => $type,
                    'delivery_category' => $deliveryCategory,
                    'specialization_mode' => $specializationMode,
                    'specialization_master_id' => $specializationMasterId,
                    'specialization_master_ids' => $specializationMasterIds,
                ];
            })
            ->values();

        // Backward compatibility with old payload.
        if ($typedSelections->isEmpty() && !empty($request->course)) {
            $fallbackType = strtoupper((string) $request->course_type);
            if (!in_array($fallbackType, [
                ProgramWiseSemesterCourse::TYPE_AUTO,
                ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE,
                ProgramWiseSemesterCourse::TYPE_DEPARTMENT_CHOICE,
            ], true)) {
                $fallbackType = ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE;
            }

            $typedSelections = collect((array) $request->course)
                ->map(fn($courseId) => [
                    'course_id' => (int) $courseId,
                    'course_type' => $fallbackType,
                    'delivery_category' => $this->normalizeDeliveryCategoryInput(data_get($request->input('delivery_category_map', []), (string) ((int) $courseId))),
                    'specialization_mode' => 'COMMON',
                    'specialization_master_id' => null,
                    'specialization_master_ids' => [],
                ])
                ->values();
        }

        if ($typedSelections->isEmpty()) {
            return $respond(false, 'Please select at least one course to map.', 422);
        }

        $selectedCourseIds = $typedSelections->pluck('course_id')->values();
        $invalidCourseIds = $selectedCourseIds->reject(fn($courseId) => in_array($courseId, $eligibleCourseIds, true))->values();

        if ($invalidCourseIds->isNotEmpty()) {
            return $respond(false, 'Please select courses only from Published Syllabi for the chosen semester.', 422);
        }

        if ($isSingleMajor) {
            if (empty($allowedSpecializationIds) && $typedSelections->contains(fn($selection) => ($selection['specialization_mode'] ?? 'COMMON') === 'SPECIALIZATION')) {
                return $respond(false, 'No active specializations are connected to this program. Please connect specializations first.', 422);
            }

            foreach ($typedSelections as $selection) {
                $specializationMode = strtoupper((string) ($selection['specialization_mode'] ?? 'COMMON'));
                $specializationMasterIds = collect((array) ($selection['specialization_master_ids'] ?? []))
                    ->map(fn($id) => (int) $id)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                if ($specializationMode !== 'SPECIALIZATION') {
                    continue;
                }

                if (empty($specializationMasterIds)) {
                    return $respond(false, 'Please select specialization for each course marked as Specialization.', 422);
                }

                foreach ($specializationMasterIds as $specializationMasterId) {
                    if (!in_array($specializationMasterId, $allowedSpecializationIds, true)) {
                        return $respond(false, 'Selected specialization is not available for this program combination.', 422);
                    }
                }
            }
        }

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($typedSelections as $index => $selection) {
            $courseId = (int) $selection['course_id'];
            $courseType = $selection['course_type'];

            $courseInfo = ProgramCourseMaster::with('coursetypemaster:id,title')->find($courseId);
            $offeringDeptId = (int) ($courseInfo->department ?? 0);
            $deliveryCategory = $selection['delivery_category'] ?? null;
            $specializationMode = strtoupper((string) ($selection['specialization_mode'] ?? 'COMMON'));
            $specializationMasterId = (int) ($selection['specialization_master_id'] ?? 0);
            $specializationMasterIds = collect((array) ($selection['specialization_master_ids'] ?? []))
                ->map(fn($value) => (int) $value)
                ->filter()
                ->unique()
                ->values()
                ->all();
            if (empty($deliveryCategory)) {
                $deliveryCategory = $this->deriveDeliveryCategory($combination, $courseInfo);
            }

            foreach ($targetAcademicPathwayIds as $targetAcademicPathwayId) {
                $existingQuery = ProgramWiseSemesterCourse::where('program_combo_refid', $refid)
                    ->where('batch', $batch)
                    ->where('semester', $semester)
                    ->where('course_id', $courseId);

                if ($hasAcademicPathwayColumn) {
                    if ($targetAcademicPathwayId === null) {
                        $existingQuery->whereNull('academic_pathway_id');
                    } else {
                        $existingQuery->where('academic_pathway_id', (int) $targetAcademicPathwayId);
                    }
                }

                if ($hasDegreeTrackColumn) {
                    $existingQuery->where('degree_track_id', $degreeTrackId);
                }

                $existing = $existingQuery->first();

                if ($existing) {
                    $existing->course_type = $courseType;
                    if ($hasDeliveryCategoryColumn) {
                        $existing->delivery_category = $deliveryCategory;
                    }
                    if ($hasSpecializationModeColumn) {
                        $existing->specialization_mode = $isSingleMajor ? $specializationMode : 'COMMON';
                    }
                    if ($hasSpecializationMasterColumn) {
                        $existing->specialization_master_id = ($isSingleMajor && $specializationMode === 'SPECIALIZATION' && $specializationMasterId > 0)
                            ? $specializationMasterId
                            : null;
                    }
                    if ($hasSpecializationMasterIdsColumn) {
                        $existing->specialization_master_ids = ($isSingleMajor && $specializationMode === 'SPECIALIZATION')
                            ? $specializationMasterIds
                            : [];
                    }
                    if ($hasOfferingDeptColumn) {
                        $existing->offering_dept = $offeringDeptId > 0 ? $offeringDeptId : null;
                    }
                    if ($hasDisplayOrderColumn) {
                        $existing->display_order = $index + 1;
                    }
                    if ($hasIsActiveColumn) {
                        $existing->is_active = true;
                    }
                    $existing->save();
                    $updatedCount++;
                    continue;
                }

                $createData = [
                    'program_combo_refid' => $refid,
                    'batch' => $batch,
                    'semester' => $semester,
                    'course_id' => $courseId,
                    'course_type' => $courseType,
                ];

                if ($hasIsActiveColumn) {
                    $createData['is_active'] = true;
                }

                if ($hasDisplayOrderColumn) {
                    $createData['display_order'] = ($index + 1);
                }

                if ($hasDeliveryCategoryColumn) {
                    $createData['delivery_category'] = $deliveryCategory;
                }

                if ($hasSpecializationModeColumn) {
                    $createData['specialization_mode'] = $isSingleMajor ? $specializationMode : 'COMMON';
                }

                if ($hasSpecializationMasterColumn) {
                    $createData['specialization_master_id'] = ($isSingleMajor && $specializationMode === 'SPECIALIZATION' && $specializationMasterId > 0)
                        ? $specializationMasterId
                        : null;
                }

                if ($hasSpecializationMasterIdsColumn) {
                    $createData['specialization_master_ids'] = ($isSingleMajor && $specializationMode === 'SPECIALIZATION')
                        ? $specializationMasterIds
                        : [];
                }

                if ($hasOfferingDeptColumn) {
                    $createData['offering_dept'] = $offeringDeptId > 0 ? $offeringDeptId : null;
                }

                if ($hasAcademicPathwayColumn) {
                    $createData['academic_pathway_id'] = $targetAcademicPathwayId;
                }

                if ($hasDegreeTrackColumn) {
                    $createData['degree_track_id'] = $degreeTrackId;
                }

                $mapping = ProgramWiseSemesterCourse::create($createData);

                if ($courseType === ProgramWiseSemesterCourse::TYPE_AUTO) {
                    $eligibleStudents = $this->getEligibleStudentsForCombination($combination);
                    if ($eligibleStudents->isNotEmpty()) {
                        $this->enrollMappedCompulsaryCourse($combination, $mapping, $eligibleStudents);
                    }
                }

                $createdCount++;
            }
        }

        $syncSummary = $this->syncCompulsoryStudentCourseMappings($combination, $semester);

        $message = 'Curriculum mapping saved.';
        if ($createdCount > 0 && $updatedCount > 0) {
            $message = 'Curriculum mapping created and updated.';
        } elseif ($updatedCount > 0 && $createdCount === 0) {
            $message = 'Curriculum mapping updated.';
        }

        if (($syncSummary['created'] ?? 0) > 0 || ($syncSummary['restored'] ?? 0) > 0) {
            $message .= ' Student-course sync completed (' . (int) ($syncSummary['created'] ?? 0) . ' mapped, ' . (int) ($syncSummary['restored'] ?? 0) . ' restored).';
        }

        return $respond(true, $message, 200, [
            'created' => $createdCount,
            'updated' => $updatedCount,
            'sync' => $syncSummary,
        ]);
    }

    public function repairProgramSemesterCoursesSync(Request $request, int $combinationId)
    {
        $isJsonRequest = $request->expectsJson() || $request->ajax();
        $respond = function (bool $status, string $message, int $httpCode = 200, array $extra = []) use ($isJsonRequest) {
            if ($isJsonRequest) {
                return response()->json(array_merge([
                    'status' => $status,
                    'message' => $message,
                ], $extra), $httpCode);
            }

            return redirect()->back()->with($status ? 'success' : 'error', $message);
        };

        $request->validate([
            'semester' => 'nullable|integer|exists:semesters,id',
        ]);

        $combination = SubjectHasStudentProgam::with([
            'batchmaster:id,batch_name',
            'studentprograminfo:id,code,name',
        ])->find($combinationId);

        if (!$combination) {
            return $respond(false, 'Program mapping not found.', 404);
        }

        $integratedContext = $this->getIntegratedSublayerContextForCombination($combination);
        if ((bool) ($integratedContext['is_integrated'] ?? false)) {
            return $respond(false, 'Repair sync is disabled for integrated programs. Use configured sublayer programs.', 422);
        }

        $semesterId = $request->filled('semester') ? (int) $request->semester : null;
        $syncSummary = $this->syncCompulsoryStudentCourseMappings($combination, $semesterId);

        $scopeLabel = $semesterId ? ('semester ' . $semesterId) : 'all semesters';
        $message = 'Student-course sync repaired for ' . $scopeLabel . '. '
            . ((int) ($syncSummary['created'] ?? 0)) . ' mapped, '
            . ((int) ($syncSummary['restored'] ?? 0)) . ' restored, '
            . ((int) ($syncSummary['existing'] ?? 0)) . ' already synced.';

        return $respond(true, $message, 200, [
            'sync' => $syncSummary,
        ]);
    }

    private function normalizeDeliveryCategoryInput(?string $value): ?string
    {
        $normalized = strtoupper(trim((string) $value));
        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['COMBO1', 'COMBO 1', 'CORE A', 'CORE-A', 'COREA', 'MAJOR_COMBO1'], true)) {
            return ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
        }

        if (in_array($normalized, ['COMBO2', 'COMBO 2', 'CORE B', 'CORE-B', 'COREB', 'MAJOR_COMBO2'], true)) {
            return ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2;
        }

        if (in_array($normalized, ['MDC', 'OPEN_CHOICE', 'OPEN CHOICE'], true)) {
            return ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE;
        }

        if (in_array($normalized, ['COMMON', 'PROGRAMME_COMMON', 'PROGRAMME COMMON'], true)) {
            return ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
        }

        return null;
    }

    function deleteProgramSemesterCourseMapping($id)
    {
        $mapping = ProgramWiseSemesterCourse::findOrFail($id);
        $combination = SubjectHasStudentProgam::with('batchmaster:id,batch_name')->find($mapping->program_combo_refid);

        $integratedContext = $this->getIntegratedSublayerContextForCombination($combination);
        if ((bool) ($integratedContext['is_integrated'] ?? false)) {
            return redirect()->back()->with('error', 'Delete is locked for integrated programs. Manage curriculum in UG/PG sublayer programs.');
        }

        $impact = $this->getMappingDeletionImpact($mapping, $combination);

        if ($mapping->course_type === ProgramWiseSemesterCourse::TYPE_AUTO && $combination) {
            if (!$impact['can_delete']) {
                return redirect()->back()->with('error', 'Cannot delete mapping. Marks or attendance exist for students in this mapped course and semester.');
            }

            $this->removeMappedCompulsaryCourseEnrollments($combination, $mapping, $impact['student_ids']);
        }

        $mapping->delete();

        return redirect()->back()->with('success', 'Mapping deleted and enrollments synced.');
    }

    function updateProgramSemesterCoursesMapping(Request $request, int $id)
    {
        $request->validate([
            'semester' => 'required|integer|exists:semesters,id',
            'course_id' => 'required|integer|exists:program_course_masters,id',
            'course_type' => 'required|in:AUTO,STUDENT_CHOICE,DEPARTMENT_CHOICE',
            'academic_pathway_id' => 'nullable|integer|exists:academic_pathway_masters,id',
            'degree_track_id' => 'nullable|integer|exists:degree_track_masters,id',
            'display_order' => 'required|integer|min:1|max:999',
        ]);

        $mapping = ProgramWiseSemesterCourse::findOrFail($id);
        $previousSemester = (int) ($mapping->semester ?? 0);
        $courseType = strtoupper((string) $request->course_type);
        $academicPathwayId = $request->filled('academic_pathway_id') ? (int) $request->academic_pathway_id : null;
        $degreeTrackId = $request->filled('degree_track_id') ? (int) $request->degree_track_id : null;
        $curriculumTable = $this->getCurriculumEngineTable();
        $hasAcademicPathwayColumn = Schema::hasColumn($curriculumTable, 'academic_pathway_id');
        $hasDegreeTrackColumn = Schema::hasColumn($curriculumTable, 'degree_track_id');
        $hasOfferingDeptColumn = Schema::hasColumn($curriculumTable, 'offering_dept');
        $hasDeliveryCategoryColumn = Schema::hasColumn($curriculumTable, 'delivery_category');
        $hasSpecializationModeColumn = Schema::hasColumn($curriculumTable, 'specialization_mode');
        $hasSpecializationMasterColumn = Schema::hasColumn($curriculumTable, 'specialization_master_id');
        $hasSpecializationMasterIdsColumn = Schema::hasColumn($curriculumTable, 'specialization_master_ids');
        $hasDisplayOrderColumn = Schema::hasColumn($curriculumTable, 'display_order');
        $hasIsActiveColumn = Schema::hasColumn($curriculumTable, 'is_active');

        if (($academicPathwayId !== null && !$hasAcademicPathwayColumn) || ($degreeTrackId !== null && !$hasDegreeTrackColumn)) {
            return redirect()->back()->with('error', 'Pathway/Track columns are missing in curriculum table. Please run latest migrations and try again.');
        }
        $combination = SubjectHasStudentProgam::with('combomap:id,student_program_id,combo_id_1,combo_id_2')->find($mapping->program_combo_refid);

        if (!$combination) {
            return redirect()->back()->with('error', 'Program mapping not found.');
        }

        $integratedContext = $this->getIntegratedSublayerContextForCombination($combination);
        if ((bool) ($integratedContext['is_integrated'] ?? false)) {
            return redirect()->back()->with('error', 'Update is locked for integrated programs. Manage curriculum in UG/PG sublayer programs.');
        }

        $comboBoundary = $this->getProgrammeBoundarySubjectIds($combination);
        $isSingleMajor = (int) ($comboBoundary['combo1'] ?? 0) > 0
            && (int) ($comboBoundary['combo1'] ?? 0) === (int) ($comboBoundary['combo2'] ?? 0);

        $specializationMode = strtoupper(trim((string) $request->input('specialization_mode', 'COMMON')));
        if (!in_array($specializationMode, ['COMMON', 'SPECIALIZATION'], true)) {
            $specializationMode = 'COMMON';
        }

        $specializationMasterIds = collect((array) $request->input('specialization_master_ids', []))
            ->map(fn($value) => (int) $value)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $specializationMasterId = $specializationMasterIds[0] ?? null;

        if ($isSingleMajor && ($hasSpecializationModeColumn || $hasSpecializationMasterColumn)) {
            $allowedSpecializationIds = collect((array) ($combination->specialization_ids ?? []))
                ->map(fn($value) => (int) $value)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($specializationMode === 'SPECIALIZATION') {
                if (empty($specializationMasterIds)) {
                    return redirect()->back()->with('error', 'Please select a specialization.');
                }

                foreach ($specializationMasterIds as $value) {
                    if (!in_array($value, $allowedSpecializationIds, true)) {
                        return redirect()->back()->with('error', 'Selected specialization is not available for this program combination.');
                    }
                }
            }
        } else {
            $specializationMode = 'COMMON';
            $specializationMasterId = null;
            $specializationMasterIds = [];
        }

        $eligibleCourseIds = $this->getEligiblePublishedCourseIdsForSemester($combination, (int) $request->semester);
        if (!in_array((int) $request->course_id, $eligibleCourseIds, true)) {
            return redirect()->back()->with('error', 'Selected course is not available in Published Syllabi for this semester.');
        }

        $duplicateQuery = ProgramWiseSemesterCourse::where('id', '!=', $mapping->id)
            ->where('program_combo_refid', $mapping->program_combo_refid)
            ->where('batch', $mapping->batch)
            ->where('semester', (int) $request->semester)
            ->where('course_id', (int) $request->course_id)
            ->where('course_type', $courseType);

        if ($hasAcademicPathwayColumn) {
            $duplicateQuery->where('academic_pathway_id', $academicPathwayId);
        }

        if ($hasDegreeTrackColumn) {
            $duplicateQuery->where('degree_track_id', $degreeTrackId);
        }

        $duplicate = $duplicateQuery->exists();

        if ($duplicate) {
            return redirect()->back()->with('error', 'Duplicate mapping exists for the same semester/course/type/pathway/track.');
        }

        $courseInfo = ProgramCourseMaster::with('coursetypemaster:id,title')->find((int) $request->course_id);
        $offeringDeptId = (int) ($courseInfo->department ?? 0);
        $deliveryCategory = $this->deriveDeliveryCategory($combination, $courseInfo);

        $mapping->semester = (int) $request->semester;
        $mapping->course_id = (int) $request->course_id;
        $mapping->course_type = $courseType;
        if ($hasDeliveryCategoryColumn) {
            $mapping->delivery_category = $deliveryCategory;
        }
        if ($hasSpecializationModeColumn) {
            $mapping->specialization_mode = $isSingleMajor ? $specializationMode : 'COMMON';
        }
        if ($hasSpecializationMasterColumn) {
            $mapping->specialization_master_id = ($isSingleMajor && $specializationMode === 'SPECIALIZATION') ? $specializationMasterId : null;
        }
        if ($hasSpecializationMasterIdsColumn) {
            $mapping->specialization_master_ids = ($isSingleMajor && $specializationMode === 'SPECIALIZATION') ? $specializationMasterIds : [];
        }
        if ($hasOfferingDeptColumn) {
            $mapping->offering_dept = $offeringDeptId > 0 ? $offeringDeptId : null;
        }
        if ($hasAcademicPathwayColumn) {
            $mapping->academic_pathway_id = $academicPathwayId;
        }
        if ($hasDegreeTrackColumn) {
            $mapping->degree_track_id = $degreeTrackId;
        }
        if ($hasDisplayOrderColumn) {
            $mapping->display_order = (int) $request->display_order;
        }
        if ($hasIsActiveColumn) {
            $mapping->is_active = $request->has('is_active');
        }
        $mapping->save();

        $this->syncCompulsoryStudentCourseMappings($combination, (int) $mapping->semester);
        if ($previousSemester > 0 && $previousSemester !== (int) $mapping->semester) {
            $this->syncCompulsoryStudentCourseMappings($combination, $previousSemester);
        }

        return redirect()->back()->with('success', 'Mapping updated successfully.');
    }

    private function deriveDeliveryCategory(?SubjectHasStudentProgam $combination, ?ProgramCourseMaster $courseInfo, ?array $boundary = null): ?string
    {
        if (!$courseInfo) {
            return null;
        }

        $boundary = $boundary ?: $this->getProgrammeBoundarySubjectIds($combination);
        $combo1SubjectId = (int) ($boundary['combo1'] ?? 0);
        $combo2SubjectId = (int) ($boundary['combo2'] ?? 0);
        $courseSubjectId = (int) ($courseInfo->department ?? 0);
        $courseTypeTitle = strtoupper(trim((string) optional($courseInfo->coursetypemaster)->title));

        if ($courseTypeTitle === 'MDC') {
            return ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE;
        }

        if ($courseTypeTitle === 'MAJ') {
            if ($combo1SubjectId > 0 && $courseSubjectId === $combo1SubjectId) {
                return ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
            }

            if ($combo2SubjectId > 0 && $courseSubjectId === $combo2SubjectId) {
                return ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2;
            }

            return ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
        }

        return ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
    }

    private function resolveSubjectToDepartmentId(int $subjectId): int
    {
        static $resolvedDepartmentBySubjectId = [];
        static $subjectHasMainDeptColumn = null;
        static $subjectsAndDepartmentTablesChecked = null;
        static $departmentHasCampusColumn = null;
        static $subjectsHasCampusColumn = null;
        static $departmentHasDepartmentCodeColumn = null;
        static $departmentHasCodeColumn = null;
        static $departmentHasNameColumn = null;
        static $departmentHasTitleColumn = null;

        if ($subjectId <= 0) {
            return 0;
        }

        if (isset($resolvedDepartmentBySubjectId[$subjectId])) {
            return $resolvedDepartmentBySubjectId[$subjectId];
        }

        if ($subjectsAndDepartmentTablesChecked === null) {
            $subjectsAndDepartmentTablesChecked = Schema::hasTable('subjects') && Schema::hasTable('department_masters');
        }

        if ($subjectHasMainDeptColumn === null) {
            $subjectHasMainDeptColumn = Schema::hasColumn('subjects', 'main_dept_id');
        }

        if ($departmentHasCampusColumn === null) {
            $departmentHasCampusColumn = Schema::hasColumn('department_masters', 'campus_id');
        }

        if ($subjectsHasCampusColumn === null) {
            $subjectsHasCampusColumn = Schema::hasColumn('subjects', 'campus_id');
        }

        if ($departmentHasDepartmentCodeColumn === null) {
            $departmentHasDepartmentCodeColumn = Schema::hasColumn('department_masters', 'department_code');
        }

        if ($departmentHasCodeColumn === null) {
            $departmentHasCodeColumn = Schema::hasColumn('department_masters', 'code');
        }

        if ($departmentHasNameColumn === null) {
            $departmentHasNameColumn = Schema::hasColumn('department_masters', 'name');
        }

        if ($departmentHasTitleColumn === null) {
            $departmentHasTitleColumn = Schema::hasColumn('department_masters', 'title');
        }

        if (!$subjectsAndDepartmentTablesChecked) {
            $resolvedDepartmentBySubjectId[$subjectId] = $subjectId;
            return $resolvedDepartmentBySubjectId[$subjectId];
        }

        $subject = Subject::query()
            ->select(['id', 'code', 'title', 'campus_id'])
            ->find($subjectId);

        if (!$subject) {
            $resolvedDepartmentBySubjectId[$subjectId] = $subjectId;
            return $resolvedDepartmentBySubjectId[$subjectId];
        }

        if ($subjectHasMainDeptColumn) {
            $directDepartmentId = (int) ($subject->main_dept_id ?? 0);
            if ($directDepartmentId > 0) {
                $resolvedDepartmentBySubjectId[$subjectId] = $directDepartmentId;
                return $resolvedDepartmentBySubjectId[$subjectId];
            }
        }

        $departmentQuery = DB::table('department_masters');

        if ($departmentHasCampusColumn && $subjectsHasCampusColumn) {
            $departmentQuery->where('campus_id', (int) ($subject->campus_id ?? 0));
        }

        $subjectCode = strtoupper(trim((string) ($subject->code ?? '')));
        if ($subjectCode !== '') {
            if ($departmentHasDepartmentCodeColumn) {
                $matchedByDeptCode = (int) (clone $departmentQuery)
                    ->whereRaw('UPPER(TRIM(department_code)) = ?', [$subjectCode])
                    ->value('id');

                if ($matchedByDeptCode > 0) {
                    $resolvedDepartmentBySubjectId[$subjectId] = $matchedByDeptCode;
                    return $resolvedDepartmentBySubjectId[$subjectId];
                }
            }

            if ($departmentHasCodeColumn) {
                $matchedByCode = (int) (clone $departmentQuery)
                    ->whereRaw('UPPER(TRIM(code)) = ?', [$subjectCode])
                    ->value('id');

                if ($matchedByCode > 0) {
                    $resolvedDepartmentBySubjectId[$subjectId] = $matchedByCode;
                    return $resolvedDepartmentBySubjectId[$subjectId];
                }
            }
        }

        $subjectTitle = strtoupper(trim((string) ($subject->title ?? '')));
        if ($subjectTitle !== '') {
            if ($departmentHasNameColumn) {
                $matchedByName = (int) (clone $departmentQuery)
                    ->whereRaw('UPPER(TRIM(name)) = ?', [$subjectTitle])
                    ->value('id');

                if ($matchedByName > 0) {
                    $resolvedDepartmentBySubjectId[$subjectId] = $matchedByName;
                    return $resolvedDepartmentBySubjectId[$subjectId];
                }
            }

            if ($departmentHasTitleColumn) {
                $matchedByTitle = (int) (clone $departmentQuery)
                    ->whereRaw('UPPER(TRIM(title)) = ?', [$subjectTitle])
                    ->value('id');

                if ($matchedByTitle > 0) {
                    $resolvedDepartmentBySubjectId[$subjectId] = $matchedByTitle;
                    return $resolvedDepartmentBySubjectId[$subjectId];
                }
            }
        }

        // Last fallback for legacy data where ids happened to align.
        $resolvedDepartmentBySubjectId[$subjectId] = $subjectId;
        return $resolvedDepartmentBySubjectId[$subjectId];
    }

    private function getEligiblePublishedCourseIdsForSemester(SubjectHasStudentProgam $combination, int $semesterId): array
    {
        $coursesBySemester = $this->getPublishedCoursesBySemesterForCombination($combination);
        return collect($coursesBySemester[(string) $semesterId] ?? [])
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();
    }

    private function getPublishedCoursesBySemesterForCombination(SubjectHasStudentProgam $combination, ?array $boundary = null): array
    {
        $boundary = $boundary ?: $this->getProgrammeBoundarySubjectIds($combination);
        $programType = $this->resolveCombinationProgramType($combination);

        $cacheKey = 'curriculum:published-by-semester:'
            . (int) ($combination->id ?? 0)
            . ':' . (int) ($combination->batch_id ?? 0)
            . ':' . $programType
            . ':' . md5(json_encode($boundary));

        return Cache::remember($cacheKey, now()->addMinutes(3), function () use ($combination, $boundary, $programType) {
            $query = SyllabusManager::with([
                'courseobjective:id,course_code,course_title,course_type,department',
                'courseobjective.coursetypemaster:id,title',
            ])
                ->where('batch_id', $combination->batch_id);

            if (Schema::hasColumn('syllabus_managers', 'program_type')) {
                $query->whereRaw("UPPER(TRIM(COALESCE(program_type, ''))) = ?", [$programType]);
            }

            if (Schema::hasColumn('syllabus_managers', 'status')) {
                $query->where('status', 'published');
            }

            $publishedSyllabus = $query->get();
            $combo1SubjectId = (int) ($boundary['combo1'] ?? 0);
            $combo2SubjectId = (int) ($boundary['combo2'] ?? 0);

            $courseSubjectIds = $publishedSyllabus
                ->pluck('courseobjective.department')
                ->map(fn($value) => (int) $value)
                ->filter(fn($value) => $value > 0)
                ->unique()
                ->values();

            $subjectMap = $courseSubjectIds->isNotEmpty()
                ? Subject::query()
                ->whereIn('id', $courseSubjectIds->all())
                ->get(['id', 'title', 'code'])
                ->keyBy('id')
                : collect();

            $bySemester = [];

            foreach ($publishedSyllabus as $syllabus) {
                $course = $syllabus->courseobjective;
                if (!$course) {
                    continue;
                }

                $courseTypeTitle = strtoupper(trim((string) optional($course->coursetypemaster)->title));
                $courseSubjectId = (int) ($course->department ?? 0);
                $courseSubject = $subjectMap->get($courseSubjectId);

                $deliveryCategory = ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
                if ($courseTypeTitle === 'MDC') {
                    $deliveryCategory = ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE;
                } elseif ($courseTypeTitle === 'MAJ') {
                    if ($combo1SubjectId > 0 && $courseSubjectId === $combo1SubjectId) {
                        $deliveryCategory = ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
                    } elseif ($combo2SubjectId > 0 && $courseSubjectId === $combo2SubjectId) {
                        $deliveryCategory = ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2;
                    }
                }

                $semesterKey = (string) ((int) $syllabus->semester_id);
                $courseKey = (string) ((int) $course->id);

                if (!isset($bySemester[$semesterKey])) {
                    $bySemester[$semesterKey] = [];
                }

                $bySemester[$semesterKey][$courseKey] = [
                    'id' => (int) $course->id,
                    'course_code' => (string) ($course->course_code ?? ''),
                    'course_title' => (string) ($course->course_title ?? ''),
                    'course_type_title' => (string) ($courseTypeTitle !== '' ? $courseTypeTitle : 'NA'),
                    'source_subject_id' => $courseSubjectId,
                    'source_subject' => (string) ($courseSubject->title ?? 'NA'),
                    'source_subject_code' => (string) ($courseSubject->code ?? ''),
                    'delivery_category' => $deliveryCategory,
                    'delivery_label' => $deliveryCategory ? strtoupper(str_replace('_', ' ', $deliveryCategory)) : 'NOT DERIVED',
                ];
            }

            foreach ($bySemester as $semesterKey => $courses) {
                $bySemester[$semesterKey] = collect($courses)
                    ->sortBy(fn($course) => ($course['course_code'] ?? '') . ' ' . ($course['course_title'] ?? ''))
                    ->values()
                    ->all();
            }

            return $bySemester;
        });
    }

    private function resolveCombinationProgramType(?SubjectHasStudentProgam $combination): string
    {
        $programType = strtoupper(trim((string) ($combination->program_type ?? 'UG')));
        return $programType === 'PG' ? 'PG' : 'UG';
    }

    private function resolveCurriculumCourseType(array $course): string
    {
        $courseTypeTitle = strtoupper(trim((string) ($course['course_type_title'] ?? '')));
        $deliveryCategory = strtoupper(trim((string) ($course['delivery_category'] ?? '')));

        if ($courseTypeTitle === 'MAJ') {
            if (in_array($deliveryCategory, ['COMBO1', 'CORE-A', 'COREA', 'MAJOR_COMBO1'], true)) {
                return 'COMBO1';
            }

            if (in_array($deliveryCategory, ['COMBO2', 'CORE-B', 'COREB', 'MAJOR_COMBO2'], true)) {
                return 'COMBO2';
            }
        }

        return $courseTypeTitle !== '' ? $courseTypeTitle : 'NA';
    }

    private function getProgrammeBoundarySubjectIds(?SubjectHasStudentProgam $combination): array
    {
        if (!$combination) {
            return [
                'combo1' => 0,
                'combo2' => 0,
                'all' => [],
            ];
        }

        $comboMap = $combination->relationLoaded('combomap')
            ? $combination->combomap
            : StdProgComboMap::where('student_program_id', (int) $combination->student_program_id)
            ->select('combo_id_1', 'combo_id_2')
            ->first();
        $mainSubjectId = (int) ($combination->subject_id ?? 0);

        $combo1Id = (int) ($comboMap->combo_id_1 ?? 0);
        if ($combo1Id <= 0) {
            $combo1Id = $mainSubjectId;
        }

        $combo2Id = (int) ($comboMap->combo_id_2 ?? 0);

        $all = collect([$combo1Id, $combo2Id, $mainSubjectId])
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        return [
            'combo1' => $combo1Id,
            'combo2' => $combo2Id,
            'all' => $all,
        ];
    }

    function updateProgramSemesterCoursesOrder(Request $request)
    {
        $curriculumTable = $this->getCurriculumEngineTable();
        $hasDisplayOrderColumn = Schema::hasColumn($curriculumTable, 'display_order');

        $request->validate([
            'combination_id' => 'required|integer|exists:subject_has_student_progams,id',
            'semester' => 'required|integer|exists:semesters,id',
            'mapping_ids' => 'required|array|min:1',
            'mapping_ids.*' => "required|integer|exists:{$curriculumTable},id",
        ]);

        $combinationId = (int) $request->combination_id;
        $semester = (int) $request->semester;
        $mappingIds = collect($request->mapping_ids)->map(fn($id) => (int) $id)->values();

        $records = ProgramWiseSemesterCourse::where('program_combo_refid', $combinationId)
            ->where('semester', $semester)
            ->whereIn('id', $mappingIds)
            ->get(['id']);

        if ($records->count() !== $mappingIds->count()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid mapping list for the selected semester.',
            ], 422);
        }

        if (!$hasDisplayOrderColumn) {
            return response()->json([
                'status' => false,
                'message' => 'Display order column is missing. Please run the migration to enable drag-and-drop ordering.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($mappingIds as $index => $mappingId) {
                ProgramWiseSemesterCourse::where('id', $mappingId)
                    ->update(['display_order' => $index + 1]);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Display order updated successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update order: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getCurriculumEngineTable(): string
    {
        if (Schema::hasTable('curriculam_engine')) {
            return 'curriculam_engine';
        }

        return 'program_wise_semester_courses';
    }

    private function enrollMappedCompulsaryCourse(SubjectHasStudentProgam $combination, ProgramWiseSemesterCourse $mapping, $students = null): void
    {
        $academicYear = (string) optional($combination->batchmaster)->batch_name;
        if ($academicYear === '') {
            $academicYear = (string) date('Y');
        }

        if ($students === null) {
            $students = $this->getEligibleStudentsForCombination($combination);
        }

        if ($students->isEmpty()) {
            return;
        }

        foreach ($students as $student) {
            $existingEnrollment = StudentCourseInfo::withTrashed()
                ->where('student_id', $student->id)
                ->where('course_id', $mapping->course_id)
                ->where('semester', $mapping->semester)
                ->where('academic_year', $academicYear)
                ->first();

            if ($existingEnrollment) {
                if (method_exists($existingEnrollment, 'trashed') && $existingEnrollment->trashed()) {
                    $existingEnrollment->restore();
                }

                if (Schema::hasColumn('student_course_infos', 'is_active') && (int) ($existingEnrollment->is_active ?? 0) !== 1) {
                    $existingEnrollment->is_active = 1;
                    $existingEnrollment->save();
                }

                continue;
            }

            StudentCourseInfo::create([
                'student_id' => $student->id,
                'course_id' => $mapping->course_id,
                'semester' => $mapping->semester,
                'campus_id' => $student->campus_id,
                'is_active' => 1,
                'academic_year' => $academicYear,
                'course_status' => 'EN',
                'is_elective' => 0,
            ]);
        }
    }

    private function removeMappedCompulsaryCourseEnrollments(SubjectHasStudentProgam $combination, ProgramWiseSemesterCourse $mapping, ?array $studentIds = null): void
    {
        $academicYear = (string) optional($combination->batchmaster)->batch_name;
        if ($academicYear === '') {
            $academicYear = (string) date('Y');
        }

        if ($studentIds === null) {
            $studentIds = $this->getEligibleStudentsForCombination($combination)->pluck('id')->toArray();
        }

        if (empty($studentIds)) {
            return;
        }

        StudentCourseInfo::whereIn('student_id', $studentIds)
            ->where('course_id', $mapping->course_id)
            ->where('semester', $mapping->semester)
            ->where('academic_year', $academicYear)
            ->delete();
    }

    private function getEligibleStudentsForCombination(SubjectHasStudentProgam $combination)
    {
        $query = StudentMaster::where('new_program_id', $combination->student_program_id)
            ->where('batch', $combination->batch_id)
            ->where('is_deleted', 0)
            ->where('is_left', 0);

        $combinationCampusId = (int) ($combination->campus_id ?? 0);
        if ($combinationCampusId > 0 && Schema::hasColumn('student_masters', 'campus_id')) {
            $query->where('campus_id', $combinationCampusId);
        }

        return $query->get(['id', 'campus_id']);
    }

    private function syncCompulsoryStudentCourseMappings(SubjectHasStudentProgam $combination, ?int $semesterId = null): array
    {
        $academicYear = (string) optional($combination->batchmaster)->batch_name;
        if ($academicYear === '') {
            $academicYear = (string) date('Y');
        }

        $eligibleStudents = $this->getEligibleStudentsForCombination($combination);
        if ($eligibleStudents->isEmpty()) {
            return [
                'mappings' => 0,
                'eligible_students' => 0,
                'created' => 0,
                'restored' => 0,
                'existing' => 0,
            ];
        }

        $mappingQuery = ProgramWiseSemesterCourse::where('program_combo_refid', (int) $combination->id)
            ->where('course_type', ProgramWiseSemesterCourse::TYPE_AUTO);

        if ($semesterId !== null && $semesterId > 0) {
            $mappingQuery->where('semester', $semesterId);
        }

        $curriculumTable = $this->getCurriculumEngineTable();
        if (Schema::hasColumn($curriculumTable, 'is_active')) {
            $mappingQuery->where('is_active', 1);
        }

        $mappings = $mappingQuery
            ->get(['id', 'course_id', 'semester'])
            ->filter(fn($mapping) => (int) ($mapping->course_id ?? 0) > 0 && (int) ($mapping->semester ?? 0) > 0)
            ->values();

        if ($mappings->isEmpty()) {
            return [
                'mappings' => 0,
                'eligible_students' => (int) $eligibleStudents->count(),
                'created' => 0,
                'restored' => 0,
                'existing' => 0,
            ];
        }

        $created = 0;
        $restored = 0;
        $existing = 0;

        foreach ($mappings as $mapping) {
            foreach ($eligibleStudents as $student) {
                $enrollment = StudentCourseInfo::withTrashed()
                    ->where('student_id', (int) $student->id)
                    ->where('course_id', (int) $mapping->course_id)
                    ->where('semester', (int) $mapping->semester)
                    ->where('academic_year', $academicYear)
                    ->first();

                if ($enrollment) {
                    if (method_exists($enrollment, 'trashed') && $enrollment->trashed()) {
                        $enrollment->restore();
                        $restored++;
                    } else {
                        $existing++;
                    }

                    if (Schema::hasColumn('student_course_infos', 'is_active') && (int) ($enrollment->is_active ?? 0) !== 1) {
                        $enrollment->is_active = 1;
                        $enrollment->save();
                    }

                    continue;
                }

                StudentCourseInfo::create([
                    'student_id' => (int) $student->id,
                    'course_id' => (int) $mapping->course_id,
                    'semester' => (int) $mapping->semester,
                    'campus_id' => (int) ($student->campus_id ?? 0),
                    'is_active' => 1,
                    'academic_year' => $academicYear,
                    'course_status' => 'EN',
                    'is_elective' => 0,
                ]);

                $created++;
            }
        }

        return [
            'mappings' => (int) $mappings->count(),
            'eligible_students' => (int) $eligibleStudents->count(),
            'created' => $created,
            'restored' => $restored,
            'existing' => $existing,
        ];
    }

    private function getMappingDeletionImpact(ProgramWiseSemesterCourse $mapping, ?SubjectHasStudentProgam $combination): array
    {
        if (!$combination || $mapping->course_type !== ProgramWiseSemesterCourse::TYPE_AUTO) {
            return [
                'eligible_students' => 0,
                'affected_enrollments' => 0,
                'marked_students' => 0,
                'can_delete' => true,
                'student_ids' => [],
            ];
        }

        $academicYear = (string) optional($combination->batchmaster)->batch_name;
        if ($academicYear === '') {
            $academicYear = (string) date('Y');
        }

        $studentIds = $this->getEligibleStudentsForCombination($combination)->pluck('id')->toArray();
        if (empty($studentIds)) {
            return [
                'eligible_students' => 0,
                'affected_enrollments' => 0,
                'marked_students' => 0,
                'can_delete' => true,
                'student_ids' => [],
            ];
        }

        $affectedEnrollments = StudentCourseInfo::whereIn('student_id', $studentIds)
            ->where('course_id', $mapping->course_id)
            ->where('semester', $mapping->semester)
            ->where('academic_year', $academicYear)
            ->count();

        $faStudentIds = InterMark::whereIn('student_id', $studentIds)
            ->where('course_id', $mapping->course_id)
            ->where('semester', $mapping->semester)
            ->pluck('student_id')
            ->toArray();

        $ciaStudentIds = CiaMark::whereIn('STUDENT_ID', $studentIds)
            ->where('COURSE_ID', $mapping->course_id)
            ->where('SEMESTER_ID', $mapping->semester)
            ->pluck('STUDENT_ID')
            ->toArray();

        $saStudentIds = DB::table('exam_marks_entries as eme')
            ->join('exam_sessions as es', 'es.id', '=', 'eme.exam_session_id')
            ->whereIn('eme.erp_student_id', $studentIds)
            ->where('eme.erp_subject_id', $mapping->course_id)
            ->where('es.semester', $mapping->semester)
            ->pluck('eme.erp_student_id')
            ->toArray();

        $attendanceStudentIds = StudentAttendance::whereIn('student_id', $studentIds)
            ->where('course_id', $mapping->course_id)
            ->pluck('student_id')
            ->toArray();

        $lockedStudents = count(array_unique(array_merge($faStudentIds, $ciaStudentIds, $saStudentIds, $attendanceStudentIds)));

        return [
            'eligible_students' => count($studentIds),
            'affected_enrollments' => $affectedEnrollments,
            'marked_students' => $lockedStudents,
            'can_delete' => $lockedStudents === 0,
            'student_ids' => $studentIds,
        ];
    }

    function teachingAssignment($id, $slug)
    {
        $subjectInfo = Subject::find($id);
        if (!$subjectInfo) {
            return redirect()->route('department.dashboard')->with('error', 'Department not found.');
        }

        $courses = SubjectCourseMaster::where('subject_id', $subjectInfo->id)
            ->with('courseMaster:id,course_code,course_title')
            ->get();

        $faculties = SubjectFacultyMaster::where('subject_id', $subjectInfo->id)
            ->with('faculty:id,USER_CODE,FIRST_NAME,LAST_NAME')
            ->get();

        $assignments = TeachingAssignment::where('subject_id', $subjectInfo->id)
            ->with([
                'course:id,course_code,course_title',
                'faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
                'primaryFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                'coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                'shiftmaster:id,title,slug',
            ])
            ->latest()
            ->get();

        $subjectShiftIds = collect($subjectInfo->shift_ids ?? [])
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values();

        $subjectHasShiftDelivery = (int) ($subjectInfo->has_shift_delivery ?? 0) === 1;
        $shiftQuery = ShiftMaster::where('is_active', 1)->orderBy('sort_order');

        if ($subjectHasShiftDelivery) {
            if ($subjectShiftIds->isNotEmpty()) {
                $shiftQuery->whereIn('id', $subjectShiftIds->all());
            } else {
                $shiftQuery->whereRaw('1 = 0');
            }
        } else {
            $commonShiftId = (int) ShiftMaster::where('slug', 'common')->value('id');
            if ($commonShiftId > 0) {
                $shiftQuery->where('id', $commonShiftId);
            } else {
                $shiftQuery->whereRaw('1 = 0');
            }
        }

        $shiftOptions = $shiftQuery->get(['id', 'title', 'slug']);

        $deliveryTypeMap = $this->getTeachingAssignmentDeliveryTypeMap(
            (int) $subjectInfo->id,
            $courses->pluck('course_master_id')->map(fn($value) => (int) $value)->filter()->unique()->values()->all()
        );

        return view('admin.subject.teaching.index', [
            'subject' => $subjectInfo,
            'courses' => $courses,
            'faculties' => $faculties,
            'assignments' => $assignments,
            'deliveryTypeMap' => $deliveryTypeMap,
            'shiftOptions' => $shiftOptions,
            'allowsMultiPrimaryFaculty' => $this->subjectAllowsMultiPrimaryFaculty($subjectInfo),
        ]);
    }

    function storeTeachingAssignment(Request $request, $subjectId)
    {
        $subject = Subject::find($subjectId);
        if (!$subject) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Department not found.'], 404);
            }
            return redirect()->back()->with('error', 'Department not found.');
        }

        $allowsMultiPrimaryFaculty = $this->subjectAllowsMultiPrimaryFaculty($subject);

        $validated = $request->validate([
            'course_id' => 'required|integer|exists:program_course_masters,id',
            'faculty_id' => 'nullable|integer|exists:faculties,id',
            'primary_faculty_ids' => 'nullable|array',
            'primary_faculty_ids.*' => 'integer|exists:faculties,id',
            'co_faculty_ids' => 'nullable|array',
            'co_faculty_ids.*' => 'integer|exists:faculties,id',
            'delivery_type' => 'nullable|string|max:100',
            'shift_id' => 'required|integer|exists:shift_masters,id',
            'allocation_group_mode' => 'required|string|in:default,new',
            'status' => 'required|in:0,1',
            'room' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $primaryFacultyIds = collect($validated['primary_faculty_ids'] ?? [])
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values();

        if ($primaryFacultyIds->isEmpty() && !empty($validated['faculty_id'])) {
            $primaryFacultyIds = collect([(int) $validated['faculty_id']]);
        }

        if ($primaryFacultyIds->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please select at least one primary faculty member.'], 422);
            }
            return redirect()->back()->with('error', 'Please select at least one primary faculty member.');
        }

        if (!$allowsMultiPrimaryFaculty && $primaryFacultyIds->count() > 1) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This department is configured for single primary faculty assignment.'], 422);
            }
            return redirect()->back()->with('error', 'This department is configured for single primary faculty assignment.');
        }

        $coFacultyIds = collect($validated['co_faculty_ids'] ?? [])
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values();

        if ($coFacultyIds->intersect($primaryFacultyIds)->isNotEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'A faculty member cannot be selected as both primary and co-faculty.'], 422);
            }
            return redirect()->back()->with('error', 'A faculty member cannot be selected as both primary and co-faculty.');
        }

        $isCourseMapped = SubjectCourseMaster::where('subject_id', $subject->id)
            ->where('course_master_id', $validated['course_id'])
            ->exists();

        if (!$isCourseMapped) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Selected course is not mapped to this department.'], 422);
            }
            return redirect()->back()->with('error', 'Selected course is not mapped to this department.');
        }

        $allSelectedFacultyIds = $primaryFacultyIds
            ->merge($coFacultyIds)
            ->unique()
            ->values();

        if ($allSelectedFacultyIds->isNotEmpty()) {
            $mappedFacultyIds = SubjectFacultyMaster::where('subject_id', $subject->id)
                ->whereIn('faculty_id', $allSelectedFacultyIds->all())
                ->pluck('faculty_id')
                ->map(fn($value) => (int) $value)
                ->unique()
                ->values();

            if ($mappedFacultyIds->count() !== $allSelectedFacultyIds->count()) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'One or more selected faculty members are not mapped to this department.'], 422);
                }
                return redirect()->back()->with('error', 'One or more selected faculty members are not mapped to this department.');
            }
        }

        $resolvedDeliveryType = $this->resolveTeachingAssignmentDeliveryType(
            (int) $subject->id,
            (int) $validated['course_id'],
            $request->input('delivery_type')
        );

        if (!$resolvedDeliveryType) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unable to resolve delivery type from curriculum. Please select a valid curriculum-mapped delivery type.'], 422);
            }
            return redirect()->back()->with('error', 'Unable to resolve delivery type from curriculum. Please select a valid curriculum-mapped delivery type.');
        }

        if (!$this->isTeachingAssignmentShiftAllowed($subject, (int) $validated['shift_id'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Selected shift is not applicable for this subject.'], 422);
            }
            return redirect()->back()->with('error', 'Selected shift is not applicable for this subject.');
        }

        $matchingAssignments = TeachingAssignment::where('subject_id', $subject->id)
            ->where('course_id', $validated['course_id'])
            ->where('delivery_type', $resolvedDeliveryType)
            ->where('shift_id', (int) $validated['shift_id'])
            ->with('primaryFacultyMembers:id')
            ->get(['id', 'faculty_id']);

        $duplicateExists = $matchingAssignments->contains(function ($existingAssignment) use ($primaryFacultyIds) {
            $existingPrimaryIds = collect($existingAssignment->primaryFacultyMembers ?? collect())
                ->pluck('id')
                ->map(fn($value) => (int) $value)
                ->filter(fn($value) => $value > 0)
                ->unique()
                ->values();

            if ($existingPrimaryIds->isEmpty() && !empty($existingAssignment->faculty_id)) {
                $existingPrimaryIds = collect([(int) $existingAssignment->faculty_id]);
            }

            return $existingPrimaryIds->intersect($primaryFacultyIds)->isNotEmpty();
        });

        if ($duplicateExists) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Duplicate entry: this course and delivery type already has one or more selected primary faculty members.'], 422);
            }
            return redirect()->back()->with('error', 'Duplicate entry: this course and delivery type already has one or more selected primary faculty members.');
        }

        $canonicalPrimaryFacultyId = (int) ($primaryFacultyIds->first() ?? 0);

        $nextAllocationGroup = $this->resolveTeachingAssignmentAllocationGroup(
            (int) $subject->id,
            (int) $validated['course_id'],
            (string) $resolvedDeliveryType,
            (int) $validated['shift_id'],
            null,
            (string) ($validated['allocation_group_mode'] ?? 'default')
        );

        $assignment = DB::transaction(function () use ($subject, $validated, $resolvedDeliveryType, $nextAllocationGroup, $primaryFacultyIds, $coFacultyIds, $canonicalPrimaryFacultyId) {
            $assignment = TeachingAssignment::create([
                'subject_id' => $subject->id,
                'course_id' => $validated['course_id'],
                'delivery_type' => $resolvedDeliveryType,
                'shift_id' => (int) $validated['shift_id'],
                'faculty_id' => $canonicalPrimaryFacultyId,
                'allocation_group' => $nextAllocationGroup,
                'is_active' => (int) $validated['status'],
                'room' => $validated['room'] ?? '',
                'remarks' => $validated['remarks'] ?? '',
            ]);

            $this->syncTeachingAssignmentFacultyRoles($assignment, $primaryFacultyIds->all(), $coFacultyIds->all());
            $this->syncRoutinesWithTeachingAssignment($assignment);

            return $assignment;
        });

        $assignment->load([
            'course:id,course_code,course_title',
            'faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
            'primaryFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
            'coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
            'shiftmaster:id,title,slug',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Teaching assignment created successfully.',
                'assignment' => $this->serializeTeachingAssignment($assignment),
            ], 201);
        }

        return redirect()->back()->with('success', 'Teaching assignment created successfully.');
    }

    function updateTeachingAssignment(Request $request, $id)
    {
        $assignment = TeachingAssignment::find($id);
        if (!$assignment) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Teaching assignment not found.'], 404);
            }
            return redirect()->back()->with('error', 'Teaching assignment not found.');
        }

        $subject = Subject::find((int) $assignment->subject_id);
        $allowsMultiPrimaryFaculty = $this->subjectAllowsMultiPrimaryFaculty($subject);

        $validated = $request->validate([
            'course_id' => 'required|integer|exists:program_course_masters,id',
            'faculty_id' => 'nullable|integer|exists:faculties,id',
            'primary_faculty_ids' => 'nullable|array',
            'primary_faculty_ids.*' => 'integer|exists:faculties,id',
            'co_faculty_ids' => 'nullable|array',
            'co_faculty_ids.*' => 'integer|exists:faculties,id',
            'delivery_type' => 'nullable|string|max:100',
            'shift_id' => 'required|integer|exists:shift_masters,id',
            'allocation_group_mode' => 'required|string|in:default,new',
            'status' => 'required|in:0,1',
            'room' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $primaryFacultyIds = collect($validated['primary_faculty_ids'] ?? [])
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values();

        if ($primaryFacultyIds->isEmpty() && !empty($validated['faculty_id'])) {
            $primaryFacultyIds = collect([(int) $validated['faculty_id']]);
        }

        if ($primaryFacultyIds->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please select at least one primary faculty member.'], 422);
            }
            return redirect()->back()->with('error', 'Please select at least one primary faculty member.');
        }

        if (!$allowsMultiPrimaryFaculty && $primaryFacultyIds->count() > 1) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This department is configured for single primary faculty assignment.'], 422);
            }
            return redirect()->back()->with('error', 'This department is configured for single primary faculty assignment.');
        }

        $coFacultyIds = collect($validated['co_faculty_ids'] ?? [])
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values();

        if ($coFacultyIds->intersect($primaryFacultyIds)->isNotEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'A faculty member cannot be selected as both primary and co-faculty.'], 422);
            }
            return redirect()->back()->with('error', 'A faculty member cannot be selected as both primary and co-faculty.');
        }

        $isCourseMapped = SubjectCourseMaster::where('subject_id', $assignment->subject_id)
            ->where('course_master_id', $validated['course_id'])
            ->exists();

        if (!$isCourseMapped) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Selected course is not mapped to this department.'], 422);
            }
            return redirect()->back()->with('error', 'Selected course is not mapped to this department.');
        }

        $allSelectedFacultyIds = $primaryFacultyIds
            ->merge($coFacultyIds)
            ->unique()
            ->values();

        if ($allSelectedFacultyIds->isNotEmpty()) {
            $mappedFacultyIds = SubjectFacultyMaster::where('subject_id', $assignment->subject_id)
                ->whereIn('faculty_id', $allSelectedFacultyIds->all())
                ->pluck('faculty_id')
                ->map(fn($value) => (int) $value)
                ->unique()
                ->values();

            if ($mappedFacultyIds->count() !== $allSelectedFacultyIds->count()) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'One or more selected faculty members are not mapped to this department.'], 422);
                }
                return redirect()->back()->with('error', 'One or more selected faculty members are not mapped to this department.');
            }
        }

        $resolvedDeliveryType = $this->resolveTeachingAssignmentDeliveryType(
            (int) $assignment->subject_id,
            (int) $validated['course_id'],
            $request->input('delivery_type')
        );

        if (!$resolvedDeliveryType) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unable to resolve delivery type from curriculum. Please select a valid curriculum-mapped delivery type.'], 422);
            }
            return redirect()->back()->with('error', 'Unable to resolve delivery type from curriculum. Please select a valid curriculum-mapped delivery type.');
        }

        if (!$subject || !$this->isTeachingAssignmentShiftAllowed($subject, (int) $validated['shift_id'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Selected shift is not applicable for this subject.'], 422);
            }
            return redirect()->back()->with('error', 'Selected shift is not applicable for this subject.');
        }

        $matchingAssignments = TeachingAssignment::where('subject_id', $assignment->subject_id)
            ->where('course_id', $validated['course_id'])
            ->where('delivery_type', $resolvedDeliveryType)
            ->where('shift_id', (int) $validated['shift_id'])
            ->where('id', '!=', $assignment->id)
            ->with('primaryFacultyMembers:id')
            ->get(['id', 'faculty_id']);

        $duplicateExists = $matchingAssignments->contains(function ($existingAssignment) use ($primaryFacultyIds) {
            $existingPrimaryIds = collect($existingAssignment->primaryFacultyMembers ?? collect())
                ->pluck('id')
                ->map(fn($value) => (int) $value)
                ->filter(fn($value) => $value > 0)
                ->unique()
                ->values();

            if ($existingPrimaryIds->isEmpty() && !empty($existingAssignment->faculty_id)) {
                $existingPrimaryIds = collect([(int) $existingAssignment->faculty_id]);
            }

            return $existingPrimaryIds->intersect($primaryFacultyIds)->isNotEmpty();
        });

        if ($duplicateExists) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Duplicate entry: this course and delivery type already has one or more selected primary faculty members.'], 422);
            }
            return redirect()->back()->with('error', 'Duplicate entry: this course and delivery type already has one or more selected primary faculty members.');
        }

        $canonicalPrimaryFacultyId = (int) ($primaryFacultyIds->first() ?? 0);

        $combinationChanged =
            (int) $assignment->course_id !== (int) $validated['course_id'] ||
            (int) $assignment->faculty_id !== $canonicalPrimaryFacultyId ||
            (string) $assignment->delivery_type !== (string) $resolvedDeliveryType ||
            (int) ($assignment->shift_id ?? 0) !== (int) $validated['shift_id'];

        $requestedGroupMode = (string) ($validated['allocation_group_mode'] ?? 'default');
        if ($combinationChanged || $requestedGroupMode === 'new') {
            $assignment->allocation_group = $this->resolveTeachingAssignmentAllocationGroup(
                (int) $assignment->subject_id,
                (int) $validated['course_id'],
                (string) $resolvedDeliveryType,
                (int) $validated['shift_id'],
                (int) $assignment->id,
                $requestedGroupMode
            );
        }

        DB::transaction(function () use ($assignment, $validated, $resolvedDeliveryType, $primaryFacultyIds, $coFacultyIds, $canonicalPrimaryFacultyId) {
            $assignment->course_id = $validated['course_id'];
            $assignment->faculty_id = $canonicalPrimaryFacultyId;
            $assignment->delivery_type = $resolvedDeliveryType;
            $assignment->shift_id = (int) $validated['shift_id'];
            $assignment->is_active = (int) $validated['status'];
            $assignment->room = $validated['room'] ?? '';
            $assignment->remarks = $validated['remarks'] ?? '';
            $assignment->save();

            $this->syncTeachingAssignmentFacultyRoles($assignment, $primaryFacultyIds->all(), $coFacultyIds->all());

            // Keep existing timetable rows in sync with assignment replacements/edits.
            $subjectCourseId = SubjectCourseMaster::query()
                ->where('subject_id', $assignment->subject_id)
                ->where('course_master_id', $assignment->course_id)
                ->value('id');

            $routineUpdatePayload = [];

            if (!empty($subjectCourseId)) {
                $routineUpdatePayload['subject_course_id'] = (int) $subjectCourseId;
            }

            $linkedRoutinesQuery = SubjectHasRoutine::query()
                ->where(function ($query) use ($assignment) {
                    $query->where('teaching_assignment_id', $assignment->id);

                    if (Schema::hasColumn('subject_has_routines', 'teaching_allocation_id')) {
                        $query->orWhere('teaching_allocation_id', $assignment->id);
                    }
                });

            if (!empty($routineUpdatePayload)) {
                (clone $linkedRoutinesQuery)->update($routineUpdatePayload);
            }

            $assignedFacultyIds = collect($assignment->allAssignedFacultyIds())
                ->map(fn($value) => (int) $value)
                ->filter(fn($value) => $value > 0)
                ->unique()
                ->values();

            if ($assignedFacultyIds->isEmpty()) {
                $assignedFacultyIds = collect([(int) $assignment->faculty_id]);
            }

            (clone $linkedRoutinesQuery)
                ->where(function ($query) use ($assignedFacultyIds) {
                    $query->whereNull('faculty_id')
                        ->orWhereNotIn('faculty_id', $assignedFacultyIds->all());
                })
                ->update([
                    'faculty_id' => (int) $assignment->faculty_id,
                ]);

            // Also backfill newly matching legacy rows created before this assignment existed.
            $this->syncRoutinesWithTeachingAssignment($assignment);
        });

        $assignment->load([
            'course:id,course_code,course_title',
            'faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
            'primaryFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
            'coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
            'shiftmaster:id,title,slug',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Teaching assignment updated successfully.',
                'assignment' => $this->serializeTeachingAssignment($assignment),
            ]);
        }

        return redirect()->back()->with('success', 'Teaching assignment updated successfully.');
    }

    function deleteTeachingAssignment($id)
    {
        $assignment = TeachingAssignment::find($id);
        if (!$assignment) {
            return redirect()->back()->with('error', 'Teaching assignment not found.');
        }

        $assignment->delete();
        return redirect()->back()->with('success', 'Teaching assignment deleted successfully.');
    }

    private function syncTeachingAssignmentFacultyRoles(TeachingAssignment $assignment, array $primaryFacultyIds = [], array $coFacultyIds = []): void
    {
        TeachingAssignmentFaculty::query()
            ->where('teaching_assignment_id', (int) $assignment->id)
            ->delete();

        $primaryFacultyCollection = collect($primaryFacultyIds)
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values();

        $rows = [];
        foreach ($primaryFacultyCollection as $primaryFacultyId) {
            $rows[] = [
                'teaching_assignment_id' => (int) $assignment->id,
                'faculty_id' => $primaryFacultyId,
                'teaching_role' => TeachingAssignment::ROLE_PRIMARY,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (collect($coFacultyIds)->map(fn($value) => (int) $value)->filter(fn($value) => $value > 0)->unique()->values() as $coFacultyId) {
            if ($primaryFacultyCollection->contains($coFacultyId)) {
                continue;
            }

            $rows[] = [
                'teaching_assignment_id' => (int) $assignment->id,
                'faculty_id' => $coFacultyId,
                'teaching_role' => TeachingAssignment::ROLE_CO_FACULTY,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($rows)) {
            TeachingAssignmentFaculty::query()->insert($rows);
        }
    }

    private function serializeTeachingAssignment(TeachingAssignment $assignment): array
    {
        $primaryFacultyCollection = $assignment->primaryFacultyMembers ?? collect();
        if ($primaryFacultyCollection->isEmpty() && !empty($assignment->faculty)) {
            $primaryFacultyCollection = collect([$assignment->faculty]);
        }

        $primaryFacultyText = $primaryFacultyCollection
            ->map(function ($faculty) {
                return trim((string) ($faculty->USER_CODE ?? '-') . ' - ' . (string) ($faculty->FIRST_NAME ?? '-') . ' ' . (string) ($faculty->LAST_NAME ?? ''));
            })
            ->filter()
            ->values()
            ->all();

        $coFacultyCollection = $assignment->coFacultyMembers ?? collect();
        $coFacultyText = $coFacultyCollection
            ->map(function ($faculty) {
                return trim((string) ($faculty->USER_CODE ?? '-') . ' - ' . (string) ($faculty->FIRST_NAME ?? '-') . ' ' . (string) ($faculty->LAST_NAME ?? ''));
            })
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $assignment->id,
            'course_text' => trim(($assignment->course->course_code ?? '-') . ' - ' . ($assignment->course->course_title ?? '-')),
            'faculty_text' => !empty($primaryFacultyText)
                ? implode(', ', $primaryFacultyText)
                : trim(($assignment->faculty->USER_CODE ?? '-') . ' - ' . ($assignment->faculty->FIRST_NAME ?? '-') . ' ' . ($assignment->faculty->LAST_NAME ?? '')),
            'primary_faculty_text' => $primaryFacultyText,
            'co_faculty_text' => $coFacultyText,
            'delivery_type' => $assignment->delivery_type,
            'shift_id' => (int) ($assignment->shift_id ?? 0),
            'shift_text' => trim((string) ($assignment->shiftmaster->title ?? $assignment->shiftmaster->slug ?? '-')),
            'allocation_group' => $assignment->allocation_group,
            'allocation_group_label' => $assignment->allocation_group_label,
            'is_active' => (int) $assignment->is_active,
            'status_label' => (int) $assignment->is_active === 1 ? 'Active' : 'Inactive',
            'room' => $assignment->room ?: '-',
            'remarks' => $assignment->remarks ?: '-',
            'room_raw' => $assignment->room ?? '',
            'remarks_raw' => $assignment->remarks ?? '',
            'course_id' => $assignment->course_id,
            'faculty_id' => $assignment->faculty_id,
            'primary_faculty_ids' => $primaryFacultyCollection
                ->pluck('id')
                ->map(fn($value) => (int) $value)
                ->filter(fn($value) => $value > 0)
                ->values()
                ->all(),
            'co_faculty_ids' => $coFacultyCollection
                ->pluck('id')
                ->map(fn($value) => (int) $value)
                ->values()
                ->all(),
        ];
    }

    private function resolveTeachingAssignmentAllocationGroup(
        int $subjectId,
        int $courseId,
        string $deliveryType,
        int $shiftId,
        ?int $ignoreAssignmentId = null,
        string $mode = 'default'
    ): int {
        $baseQuery = TeachingAssignment::query()
            ->where('subject_id', $subjectId)
            ->where('course_id', $courseId)
            ->where('delivery_type', $deliveryType);

        if (!empty($ignoreAssignmentId)) {
            $baseQuery->where('id', '!=', $ignoreAssignmentId);
        }

        $normalizedMode = strtolower(trim($mode));
        if (!in_array($normalizedMode, ['default', 'new'], true)) {
            $normalizedMode = 'default';
        }

        $sameShiftQuery = (clone $baseQuery)->where('shift_id', $shiftId);
        $sameShiftMaxGroup = (int) ($sameShiftQuery->max('allocation_group') ?? 0);

        if ($normalizedMode === 'new') {
            $baseMaxGroup = (int) ((clone $baseQuery)->max('allocation_group') ?? 0);
            $maxGroup = max($baseMaxGroup, $sameShiftMaxGroup);
            return $maxGroup > 0 ? $maxGroup + 1 : 1;
        }

        $sameShiftGroup = (int) ((clone $sameShiftQuery)->orderBy('allocation_group')->value('allocation_group') ?? 0);
        if ($sameShiftGroup > 0) {
            return $sameShiftGroup;
        }

        // Rule: If shift is different, do not create a new group.
        $existingGroupAnyShift = (int) ((clone $baseQuery)->orderBy('allocation_group')->value('allocation_group') ?? 0);
        if ($existingGroupAnyShift > 0) {
            return $existingGroupAnyShift;
        }

        return 1;
    }

    private function isTeachingAssignmentShiftAllowed(Subject $subject, int $shiftId): bool
    {
        if ($shiftId <= 0) {
            return false;
        }

        $hasShiftDelivery = (int) ($subject->has_shift_delivery ?? 0) === 1;

        if ($hasShiftDelivery) {
            $subjectShiftIds = collect($subject->shift_ids ?? [])
                ->map(fn($value) => (int) $value)
                ->filter(fn($value) => $value > 0)
                ->unique()
                ->values()
                ->all();

            return in_array($shiftId, $subjectShiftIds, true);
        }

        $commonShiftId = (int) ShiftMaster::where('slug', 'common')->value('id');
        return $commonShiftId > 0 && $commonShiftId === $shiftId;
    }

    private function getTeachingAssignmentDeliveryTypeMap(int $subjectId, array $courseIds): array
    {
        $courseIds = collect($courseIds)
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($courseIds)) {
            return [];
        }

        $curriculumTable = $this->getCurriculumEngineTable();
        if (!Schema::hasTable($curriculumTable) || !Schema::hasColumn($curriculumTable, 'delivery_category')) {
            return [];
        }

        $hasOfferingDeptColumn = Schema::hasColumn($curriculumTable, 'offering_dept');

        $baseQuery = ProgramWiseSemesterCourse::query()
            ->whereIn('course_id', $courseIds)
            ->whereNotNull('delivery_category')
            ->select(['course_id', 'delivery_category']);

        $collectByCourse = function ($rows) {
            $mapped = [];

            foreach ($rows as $row) {
                $courseId = (int) ($row->course_id ?? 0);
                if ($courseId <= 0) {
                    continue;
                }

                $normalized = $this->normalizeDeliveryCategoryInput((string) ($row->delivery_category ?? ''));
                if (!$normalized) {
                    continue;
                }

                if (!isset($mapped[$courseId])) {
                    $mapped[$courseId] = [];
                }

                if (!in_array($normalized, $mapped[$courseId], true)) {
                    $mapped[$courseId][] = $normalized;
                }
            }

            return $mapped;
        };

        $fallbackMap = $collectByCourse((clone $baseQuery)->get());
        $effectiveMap = $fallbackMap;

        if ($hasOfferingDeptColumn) {
            $subjectScopedMap = $collectByCourse(
                (clone $baseQuery)
                    ->where('offering_dept', $subjectId)
                    ->get()
            );

            $effectiveMap = [];
            foreach ($courseIds as $courseId) {
                $effectiveMap[$courseId] = $subjectScopedMap[$courseId] ?? $fallbackMap[$courseId] ?? [];
            }
        }

        $sortOrder = [
            ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1 => 1,
            ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2 => 2,
            ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON => 3,
            ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE => 4,
        ];

        foreach ($effectiveMap as $courseId => $types) {
            usort($types, function ($left, $right) use ($sortOrder) {
                $leftRank = $sortOrder[$left] ?? 99;
                $rightRank = $sortOrder[$right] ?? 99;

                if ($leftRank === $rightRank) {
                    return strcmp((string) $left, (string) $right);
                }

                return $leftRank <=> $rightRank;
            });

            $effectiveMap[$courseId] = array_values(array_unique($types));
        }

        return $effectiveMap;
    }

    private function resolveTeachingAssignmentDeliveryType(int $subjectId, int $courseId, ?string $requestedDeliveryType): ?string
    {
        if ($courseId <= 0) {
            return null;
        }

        $allowedTypes = $this->getTeachingAssignmentDeliveryTypeMap($subjectId, [$courseId]);
        $typesForCourse = $allowedTypes[$courseId] ?? [];
        $normalizedRequestedType = $this->normalizeDeliveryCategoryInput($requestedDeliveryType);

        // Flexibility fallback: if curriculum has no mapped delivery type for this
        // subject/course (e.g., no combination configured yet), allow COMMON.
        if (empty($typesForCourse)) {
            return $normalizedRequestedType ?: ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
        }

        if (count($typesForCourse) === 1) {
            return (string) $typesForCourse[0];
        }

        if (!empty($typesForCourse) && $normalizedRequestedType && in_array($normalizedRequestedType, $typesForCourse, true)) {
            return $normalizedRequestedType;
        }

        return null;
    }

    private function syncRoutinesWithTeachingAssignment(TeachingAssignment $assignment): void
    {
        if (!Schema::hasColumn('subject_has_routines', 'teaching_assignment_id')) {
            return;
        }

        if (!$assignment->relationLoaded('primaryFacultyMembers') || !$assignment->relationLoaded('coFacultyMembers')) {
            $assignment->load([
                'primaryFacultyMembers:id',
                'coFacultyMembers:id',
            ]);
        }

        $subjectCourseIds = SubjectCourseMaster::query()
            ->where('subject_id', $assignment->subject_id)
            ->where('course_master_id', $assignment->course_id)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->filter()
            ->values();

        if ($subjectCourseIds->isEmpty()) {
            return;
        }

        $assignedFacultyIds = collect($assignment->allAssignedFacultyIds())
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values();

        if ($assignedFacultyIds->isEmpty()) {
            $assignedFacultyIds = collect([(int) ($assignment->faculty_id ?? 0)])
                ->filter(fn($value) => $value > 0)
                ->values();
        }

        if ($assignedFacultyIds->isEmpty()) {
            return;
        }

        $hasTeachingAllocationId = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

        $query = SubjectHasRoutine::query()
            ->whereIn('faculty_id', $assignedFacultyIds->all())
            ->whereIn('subject_course_id', $subjectCourseIds)
            ->whereNull('teaching_assignment_id');

        if ($hasTeachingAllocationId) {
            $query->whereNull('teaching_allocation_id');
        }

        $updatePayload = [
            'teaching_assignment_id' => (int) $assignment->id,
        ];

        if ($hasTeachingAllocationId) {
            $updatePayload['teaching_allocation_id'] = (int) $assignment->id;
        }

        $query->update($updatePayload);
    }

    function mySpecializations(Request $request, int $id, $slug)
    {
        $subject = Subject::find($id);
        if (!$subject) {
            return redirect()->back()->with('error', 'Subject not found.');
        }

        $data = SpecializationMaster::where('subject_id', $id)->latest()->get();
        $activeSpecializations = $data->where('is_active', 1)->values();
        $specializationLookup = $data->keyBy('id');

        $selectedBatchId = (int) $request->query('batch', 0);
        $selectedProgramComboId = (int) $request->query('program_combo', 0);
        $studentSearch = trim((string) $request->query('student_search', ''));
        $selectedIntegratedLayer = strtolower(trim((string) $request->query('integrated_layer', 'all')));
        if (!in_array($selectedIntegratedLayer, ['all', 'ug', 'pg'], true)) {
            $selectedIntegratedLayer = 'all';
        }

        $batchOptions = BatchMaster::orderByDesc('id')->get(['id', 'batch_name']);
        $offeredProgramCombinations = collect();
        $selectedProgramCombination = null;
        $availableSpecializationsForSelectedProgram = collect();
        $students = collect();
        $studentAssignmentMap = collect();
        $showIntegratedLayerFilter = false;
        $integratedLayerOptions = collect();
        $integratedProgramIdsWithSublayers = collect();
        $integratedProgramSettings = collect();

        if ($selectedBatchId > 0) {
            $activeSpecializationIds = $activeSpecializations->pluck('id')->map(fn($v) => (int) $v)->all();

            $offeredProgramCombinations = SubjectHasStudentProgam::with([
                'studentprograminfo:id,name,program_type',
                'studentprograminfo.programtypemaster:id,name',
            ])->where('subject_id', (int) $subject->id)
                ->where('batch_id', $selectedBatchId)
                ->orderBy('student_program_id')
                ->get(['id', 'student_program_id', 'batch_id', 'program_type', 'specialization_ids'])
                ->filter(function ($combination) use ($activeSpecializationIds) {
                    $specializationIds = collect($combination->specialization_ids ?? [])
                        ->map(fn($v) => (int) $v)
                        ->filter(fn($v) => $v > 0)
                        ->values();

                    if ($specializationIds->isEmpty() || empty($activeSpecializationIds)) {
                        return false;
                    }

                    return $specializationIds->intersect($activeSpecializationIds)->isNotEmpty();
                })
                ->values();

            if (Schema::hasTable('integrated_program_sublayer_settings')) {
                $offeredProgramIds = $offeredProgramCombinations
                    ->pluck('student_program_id')
                    ->map(fn($v) => (int) $v)
                    ->filter(fn($v) => $v > 0)
                    ->unique()
                    ->values();

                if ($offeredProgramIds->isNotEmpty()) {
                    $integratedProgramSettings = DB::table('integrated_program_sublayer_settings')
                        ->whereIn('student_program_id', $offeredProgramIds->all())
                        ->where('is_active', 1)
                        ->get(['student_program_id', 'ug_max_year', 'ug_label', 'pg_label'])
                        ->keyBy(fn($row) => (int) ($row->student_program_id ?? 0));

                    $integratedProgramIdsWithSublayers = $integratedProgramSettings->keys()->map(fn($v) => (int) $v)->values();
                }
            }

            $selectedProgramCombination = $offeredProgramCombinations->firstWhere('id', $selectedProgramComboId);
            if ($selectedProgramCombination) {
                $selectedProgramId = (int) ($selectedProgramCombination->student_program_id ?? 0);
                $integratedLayerMeta = $integratedProgramSettings->get($selectedProgramId);
                $hasCurrentYearColumn = Schema::hasColumn('student_masters', 'current_year');
                $showIntegratedLayerFilter = !empty($integratedLayerMeta) && $hasCurrentYearColumn;

                if ($showIntegratedLayerFilter) {
                    $ugMaxYear = max(1, (int) ($integratedLayerMeta->ug_max_year ?? 2));
                    $ugLabel = trim((string) ($integratedLayerMeta->ug_label ?? 'UG Layer'));
                    $pgLabel = trim((string) ($integratedLayerMeta->pg_label ?? 'PG Layer'));
                    $integratedLayerOptions = collect([
                        ['value' => 'all', 'label' => 'All Layers'],
                        ['value' => 'ug', 'label' => $ugLabel . ' (Year 1-' . $ugMaxYear . ')'],
                        ['value' => 'pg', 'label' => $pgLabel . ' (Year ' . ($ugMaxYear + 1) . '+)'],
                    ]);
                } else {
                    $selectedIntegratedLayer = 'all';
                }

                $allowedSpecializationIds = collect($selectedProgramCombination->specialization_ids ?? [])
                    ->map(fn($v) => (int) $v)
                    ->filter(fn($v) => $v > 0)
                    ->values();

                $availableSpecializationsForSelectedProgram = $activeSpecializations
                    ->filter(fn($item) => $allowedSpecializationIds->contains((int) $item->id))
                    ->values();

                $studentsQuery = StudentMaster::query()
                    ->select(['id', 'roll_no', 'first_name', 'last_name', 'new_program_id', 'batch', 'current_year'])
                    ->where('batch', $selectedBatchId)
                    ->where('new_program_id', (int) $selectedProgramCombination->student_program_id);

                if ($showIntegratedLayerFilter) {
                    $ugMaxYear = max(1, (int) ($integratedLayerMeta->ug_max_year ?? 2));
                    if ($selectedIntegratedLayer === 'ug' && $ugMaxYear > 0) {
                        $studentsQuery->whereNotNull('current_year')->where('current_year', '<=', $ugMaxYear);
                    } elseif ($selectedIntegratedLayer === 'pg' && $ugMaxYear > 0) {
                        $studentsQuery->whereNotNull('current_year')->where('current_year', '>=', $ugMaxYear + 1);
                    }
                }

                if (Schema::hasColumn('student_masters', 'is_deleted')) {
                    $studentsQuery->where('is_deleted', 0);
                }

                if (Schema::hasColumn('student_masters', 'is_left')) {
                    $studentsQuery->where('is_left', 0);
                }

                if ($studentSearch !== '') {
                    $studentsQuery->where(function ($query) use ($studentSearch) {
                        $query->where('roll_no', 'like', '%' . $studentSearch . '%')
                            ->orWhere('first_name', 'like', '%' . $studentSearch . '%')
                            ->orWhere('last_name', 'like', '%' . $studentSearch . '%');
                    });
                }

                $students = $studentsQuery
                    ->orderBy('roll_no')
                    ->orderBy('first_name')
                    ->get();

                if ($students->isNotEmpty()) {
                    $assignmentQuery = DB::table('student_specializations')
                        ->where('subject_has_student_program_id', (int) $selectedProgramCombination->id)
                        ->whereIn('student_id', $students->pluck('id')->all());

                    if (Schema::hasColumn('student_specializations', 'deleted_at')) {
                        $assignmentQuery->whereNull('deleted_at');
                    }

                    if (Schema::hasColumn('student_specializations', 'is_active')) {
                        $assignmentQuery->where('is_active', 1);
                    }

                    $studentAssignmentMap = $assignmentQuery
                        ->select('student_id', 'specialization_id', 'semester_id')
                        ->orderByDesc('id')
                        ->get()
                        ->unique('student_id')
                        ->keyBy('student_id');
                }
            }
        }

        return view('admin.subject.specialization', [
            'data' => $data,
            'subject' => $subject,
            'batchOptions' => $batchOptions,
            'selectedBatchId' => $selectedBatchId,
            'offeredProgramCombinations' => $offeredProgramCombinations,
            'selectedProgramComboId' => $selectedProgramComboId,
            'selectedProgramCombination' => $selectedProgramCombination,
            'availableSpecializationsForSelectedProgram' => $availableSpecializationsForSelectedProgram,
            'students' => $students,
            'studentSearch' => $studentSearch,
            'selectedIntegratedLayer' => $selectedIntegratedLayer,
            'showIntegratedLayerFilter' => $showIntegratedLayerFilter,
            'integratedLayerOptions' => $integratedLayerOptions,
            'integratedProgramIdsWithSublayers' => $integratedProgramIdsWithSublayers,
            'studentAssignmentMap' => $studentAssignmentMap,
            'specializationLookup' => $specializationLookup,
        ]);
    }

    function storeMySpecialization(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'subject_id' => 'required|integer|exists:subjects,id',
            'status' => 'required|in:0,1',
        ]);

        SpecializationMaster::create([
            'subject_id' => $request->subject_id,
            'slug' => Str::slug($request->name),
            'name' => $request->name,
            'is_active' => (int) $request->status,
        ]);

        return redirect()->back()->with('success', 'Created');
    }

    function updateMySpecialization(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'status' => 'required|in:0,1',
        ]);

        $specialization = SpecializationMaster::find($id);
        if (!$specialization) {
            return redirect()->back()->with('error', 'Specialization not found.');
        }

        $specialization->update([
            'slug' => Str::slug($request->name),
            'name' => $request->name,
            'is_active' => (int) $request->status,
        ]);

        return redirect()->back()->with('success', 'Updated successfully.');
    }

    function storeStudentSpecializationAssignments(Request $request, int $id, $slug)
    {
        $request->validate([
            'batch' => 'required|integer|exists:batch_masters,id',
            'program_combo_id' => 'required|integer|exists:subject_has_student_progams,id',
            'assignment_action' => ['required', Rule::in(['assign', 'reset'])],
            'specialization_id' => ['nullable', 'integer', Rule::exists('specialization_masters', 'id')],
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|integer|exists:student_masters,id',
            'student_search' => 'nullable|string|max:100',
            'integrated_layer' => ['nullable', Rule::in(['all', 'ug', 'pg'])],
        ]);

        $subject = Subject::find((int) $id);
        if (!$subject) {
            return redirect()->back()->with('error', 'Subject not found.');
        }

        $batchId = (int) $request->batch;
        $programComboId = (int) $request->program_combo_id;
        $assignmentAction = (string) $request->input('assignment_action', 'assign');
        $specializationId = (int) $request->input('specialization_id', 0);
        $studentIds = collect($request->input('student_ids', []))->map(fn($v) => (int) $v)->filter(fn($v) => $v > 0)->unique()->values();

        $programCombination = SubjectHasStudentProgam::where('id', $programComboId)
            ->where('subject_id', (int) $subject->id)
            ->where('batch_id', $batchId)
            ->first();

        if (!$programCombination) {
            return redirect()->back()->with('error', 'Invalid program combination for selected batch.');
        }

        if ($assignmentAction === 'assign') {
            if ($specializationId <= 0) {
                return redirect()->back()->with('error', 'Please select specialization to assign.');
            }

            $specialization = SpecializationMaster::where('id', $specializationId)
                ->where('subject_id', (int) $subject->id)
                ->where('is_active', 1)
                ->first();

            if (!$specialization) {
                return redirect()->back()->with('error', 'Please select an active specialization from this department.');
            }

            $allowedSpecializationIds = collect($programCombination->specialization_ids ?? [])
                ->map(fn($v) => (int) $v)
                ->filter(fn($v) => $v > 0)
                ->values();

            if (!$allowedSpecializationIds->contains($specializationId)) {
                return redirect()->back()->with('error', 'Selected specialization is not offered for the selected program.');
            }
        }

        $eligibleStudentsQuery = StudentMaster::query()
            ->where('batch', $batchId)
            ->where('new_program_id', (int) $programCombination->student_program_id)
            ->whereIn('id', $studentIds->all());

        if (Schema::hasColumn('student_masters', 'is_deleted')) {
            $eligibleStudentsQuery->where('is_deleted', 0);
        }

        if (Schema::hasColumn('student_masters', 'is_left')) {
            $eligibleStudentsQuery->where('is_left', 0);
        }

        $eligibleStudentIds = $eligibleStudentsQuery->pluck('id')->map(fn($v) => (int) $v)->values();

        if ($eligibleStudentIds->isEmpty()) {
            return redirect()->back()->with('error', 'No eligible students found for assignment.');
        }

        $hasSemesterColumn = Schema::hasColumn('student_specializations', 'semester_id');
        $hasActiveColumn = Schema::hasColumn('student_specializations', 'is_active');
        $hasDeletedAt = Schema::hasColumn('student_specializations', 'deleted_at');
        $specializationColumnMeta = DB::selectOne("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_specializations' AND COLUMN_NAME = 'specialization_id'");
        $canSpecializationBeNull = strtoupper((string) ($specializationColumnMeta->IS_NULLABLE ?? 'NO')) === 'YES';

        DB::transaction(function () use ($eligibleStudentIds, $programComboId, $specializationId, $assignmentAction, $hasSemesterColumn, $hasActiveColumn, $hasDeletedAt, $canSpecializationBeNull) {
            foreach ($eligibleStudentIds as $studentId) {
                $existingQuery = DB::table('student_specializations')
                    ->where('student_id', (int) $studentId)
                    ->where('subject_has_student_program_id', $programComboId);

                if ($hasSemesterColumn) {
                    $existingQuery->whereNull('semester_id');
                }

                $existing = $existingQuery->orderByDesc('id')->first();

                if ($existing) {
                    $updatePayload = ['updated_at' => now()];
                    if ($assignmentAction === 'assign') {
                        $updatePayload['specialization_id'] = $specializationId;
                        if ($hasActiveColumn) {
                            $updatePayload['is_active'] = 1;
                        }
                        if ($hasDeletedAt) {
                            $updatePayload['deleted_at'] = null;
                        }
                    } else {
                        if ($canSpecializationBeNull) {
                            $updatePayload['specialization_id'] = null;
                        }
                        if ($hasActiveColumn) {
                            $updatePayload['is_active'] = 0;
                        }
                        if ($hasDeletedAt) {
                            $updatePayload['deleted_at'] = now();
                        }
                    }

                    DB::table('student_specializations')
                        ->where('id', (int) $existing->id)
                        ->update($updatePayload);
                } else {
                    if ($assignmentAction === 'reset') {
                        continue;
                    }

                    $insertPayload = [
                        'student_id' => (int) $studentId,
                        'subject_has_student_program_id' => $programComboId,
                        'specialization_id' => $specializationId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if ($hasSemesterColumn) {
                        $insertPayload['semester_id'] = null;
                    }

                    if ($hasActiveColumn) {
                        $insertPayload['is_active'] = 1;
                    }

                    if ($hasDeletedAt) {
                        $insertPayload['deleted_at'] = null;
                    }

                    DB::table('student_specializations')->insert($insertPayload);
                }
            }
        });

        $queryParams = [
            'batch' => $batchId,
            'program_combo' => $programComboId,
        ];

        $integratedLayer = strtolower(trim((string) $request->input('integrated_layer', 'all')));
        if (in_array($integratedLayer, ['all', 'ug', 'pg'], true) && $integratedLayer !== 'all') {
            $queryParams['integrated_layer'] = $integratedLayer;
        }

        $search = trim((string) $request->input('student_search', ''));
        if ($search !== '') {
            $queryParams['student_search'] = $search;
        }

        return redirect()
            ->route('department.specialization.master', ['id' => $id, 'slug' => $slug] + $queryParams)
            ->with('success', $assignmentAction === 'reset'
                ? 'Specialization reset for ' . $eligibleStudentIds->count() . ' student(s).'
                : 'Specialization assigned to ' . $eligibleStudentIds->count() . ' student(s).');
    }


    function studentGroupAllotment(Request $request, $id, $slug)
    {
        $subject  = Subject::find($id);
        if (!$subject) {
            return redirect()->back()->with('error', 'Subject not found.');
        }

        $batchOptions = BatchMaster::orderByDesc('id')->get(['id', 'batch_name']);
        $selectedBatchId = (int) $request->query('batch', 0);

        if ($selectedBatchId <= 0) {
            $selectedBatchId = (int) BatchMaster::where('admission_active_batch', 1)->value('id');
        }

        $offeredCoursesByProgramType = collect();

        $normalizeProgramType = function ($programTypeValue, $programTypeMasterName = null) {
            $raw = strtoupper(trim((string) ($programTypeValue ?? '')));
            if ($raw !== '') {
                if (str_contains($raw, 'UG') || str_contains($raw, 'UNDER')) {
                    return 'UG';
                }

                if (str_contains($raw, 'PG') || str_contains($raw, 'POST')) {
                    return 'PG';
                }
            }

            $masterRaw = strtoupper(trim((string) ($programTypeMasterName ?? '')));
            if ($masterRaw !== '') {
                if (str_contains($masterRaw, 'UG') || str_contains($masterRaw, 'UNDER')) {
                    return 'UG';
                }

                if (str_contains($masterRaw, 'PG') || str_contains($masterRaw, 'POST')) {
                    return 'PG';
                }
            }

            return 'UNSPECIFIED';
        };

        if ($selectedBatchId > 0) {
            $combinations = SubjectHasStudentProgam::with([
                'studentprograminfo:id,program_type',
                'studentprograminfo.programtypemaster:id,name',
            ])->where('subject_id', (int) $subject->id)
                ->where('batch_id', $selectedBatchId)
                ->get(['id', 'student_program_id', 'batch_id', 'campus_id', 'program_type']);

            $combinationIds = $combinations->pluck('id')->filter()->map(fn($v) => (int) $v)->unique()->values();
            $programIds = $combinations->pluck('student_program_id')->filter()->map(fn($v) => (int) $v)->unique()->values();
            $programTypeByCombinationId = [];
            $programTypeByProgramId = [];

            foreach ($combinations as $combination) {
                $combinationId = (int) ($combination->id ?? 0);
                $programId = (int) ($combination->student_program_id ?? 0);

                if ($combinationId <= 0 || $programId <= 0) {
                    continue;
                }

                $resolvedProgramType = $normalizeProgramType(
                    $combination->program_type,
                    optional($combination->studentprograminfo?->programtypemaster)->name
                );

                $programTypeByCombinationId[$combinationId] = $resolvedProgramType;

                if (!isset($programTypeByProgramId[$programId])) {
                    $programTypeByProgramId[$programId] = $resolvedProgramType;
                }
            }

            if ($combinationIds->isNotEmpty()) {
                $offeredMappingsQuery = ProgramWiseSemesterCourse::whereIn('program_combo_refid', $combinationIds->all());

                if (Schema::hasColumn($this->getCurriculumEngineTable(), 'is_active')) {
                    $offeredMappingsQuery->where('is_active', 1);
                }

                $offeredMappings = $offeredMappingsQuery
                    ->get(['program_combo_refid', 'course_id', 'semester', 'course_type'])
                    ->filter(function ($row) {
                        return (int) ($row->course_id ?? 0) > 0 && (int) ($row->semester ?? 0) > 0;
                    })
                    ->values();

                $courseIds = $offeredMappings->pluck('course_id')->map(fn($v) => (int) $v)->unique()->values();
                $semesterIds = $offeredMappings->pluck('semester')->map(fn($v) => (int) $v)->unique()->values();

                if ($courseIds->isNotEmpty()) {
                    $courseMap = ProgramCourseMaster::with(['papertypemaster:id,name'])
                        ->whereIn('id', $courseIds->all())
                        ->get()
                        ->keyBy('id');

                    $semesterTitleMap = Semester::whereIn('id', $semesterIds->all())
                        ->pluck('title', 'id');

                    $matrix = [];
                    $offeredKeySet = [];

                    foreach ($offeredMappings as $map) {
                        $combinationId = (int) ($map->program_combo_refid ?? 0);
                        $programTypeLabel = $programTypeByCombinationId[$combinationId] ?? 'UNSPECIFIED';
                        $courseId = (int) $map->course_id;
                        $semesterId = (int) $map->semester;
                        $course = $courseMap->get($courseId);

                        if (!$course) {
                            continue;
                        }

                        $offeredKeySet[$programTypeLabel][$courseId . '_' . $semesterId] = true;

                        if (!isset($matrix[$programTypeLabel])) {
                            $matrix[$programTypeLabel] = [];
                        }

                        if (!isset($matrix[$programTypeLabel][$semesterId])) {
                            $matrix[$programTypeLabel][$semesterId] = [
                                'semester_id' => $semesterId,
                                'semester_title' => $semesterTitleMap->get($semesterId) ?? ('Semester ' . $semesterId),
                                'courses' => [],
                            ];
                        }

                        if (!isset($matrix[$programTypeLabel][$semesterId]['courses'][$courseId])) {
                            $matrix[$programTypeLabel][$semesterId]['courses'][$courseId] = [
                                'course_id' => $courseId,
                                'course_code' => $course->course_code ?? '-',
                                'course_title' => $course->course_title ?? '-',
                                'paper_type' => $course->papertypemaster->name ?? '-',
                                'course_types' => [],
                                'students' => [],
                                'student_count' => 0,
                            ];
                        }

                        $courseTypeLabel = strtoupper((string) ($map->course_type ?? ''));
                        if ($courseTypeLabel !== '') {
                            $matrix[$programTypeLabel][$semesterId]['courses'][$courseId]['course_types'][$courseTypeLabel] = $courseTypeLabel;
                        }
                    }

                    if (!empty($offeredKeySet) && $programIds->isNotEmpty()) {
                        $enrollmentQuery = DB::table('student_course_infos as sci')
                            ->join('student_masters as sm', 'sm.id', '=', 'sci.student_id')
                            ->whereIn('sci.course_id', $courseIds->all())
                            ->where('sm.batch', $selectedBatchId)
                            ->whereIn('sm.new_program_id', $programIds->all())
                            ->where('sm.is_deleted', 0)
                            ->where('sm.is_left', 0)
                            ->select([
                                'sci.id as student_course_info_id',
                                'sci.student_id',
                                'sci.course_id',
                                'sci.semester',
                                'sm.new_program_id',
                                'sm.roll_no',
                                'sm.first_name',
                                'sm.last_name',
                            ]);

                        if (Schema::hasColumn('student_course_infos', 'allocation_group_id')) {
                            $enrollmentQuery->addSelect('sci.allocation_group_id');
                        }

                        if (Schema::hasColumn('student_course_infos', 'is_deleted')) {
                            $enrollmentQuery->where('sci.is_deleted', 0);
                        }

                        $enrolledRows = $enrollmentQuery
                            ->orderBy('sci.semester')
                            ->orderBy('sci.course_id')
                            ->orderBy('sm.roll_no')
                            ->get()
                            ->unique(fn($row) => ((int) $row->student_id) . '_' . ((int) $row->course_id) . '_' . ((int) $row->semester))
                            ->values();

                        foreach ($enrolledRows as $row) {
                            $courseId = (int) ($row->course_id ?? 0);
                            $semesterId = (int) ($row->semester ?? 0);
                            $programId = (int) ($row->new_program_id ?? 0);
                            $programTypeLabel = $programTypeByProgramId[$programId] ?? 'UNSPECIFIED';
                            $offeredKey = $courseId . '_' . $semesterId;

                            if (!isset($offeredKeySet[$programTypeLabel][$offeredKey])) {
                                continue;
                            }

                            if (!isset($matrix[$programTypeLabel][$semesterId]['courses'][$courseId])) {
                                continue;
                            }

                            $fullName = trim(((string) ($row->first_name ?? '')) . ' ' . ((string) ($row->last_name ?? '')));
                            $matrix[$programTypeLabel][$semesterId]['courses'][$courseId]['students'][] = [
                                'student_course_info_id' => (int) ($row->student_course_info_id ?? 0),
                                'student_id' => (int) $row->student_id,
                                'roll_no' => (string) ($row->roll_no ?? '-'),
                                'name' => $fullName !== '' ? $fullName : '-',
                                'allocation_group_id' => isset($row->allocation_group_id) ? (int) ($row->allocation_group_id ?? 0) : null,
                            ];
                        }
                    }

                    foreach ($matrix as &$programTypeGroup) {
                        foreach ($programTypeGroup as &$semesterData) {
                            foreach ($semesterData['courses'] as &$courseData) {
                                $courseData['course_types'] = array_values($courseData['course_types']);
                                sort($courseData['course_types']);

                                $courseData['students'] = collect($courseData['students'])
                                    ->unique('student_id')
                                    ->sortBy('roll_no')
                                    ->values()
                                    ->all();

                                $courseData['student_count'] = count($courseData['students']);
                            }

                            $semesterData['courses'] = collect($semesterData['courses'])
                                ->sortBy('course_code')
                                ->values()
                                ->all();
                        }
                        $programTypeGroup = collect($programTypeGroup)
                            ->sortBy('semester_id')
                            ->values()
                            ->all();
                    }
                    unset($programTypeGroup, $semesterData, $courseData);

                    $orderedProgramTypes = ['UG', 'PG', 'UNSPECIFIED'];
                    $offeredCoursesByProgramType = collect($orderedProgramTypes)
                        ->filter(fn($type) => isset($matrix[$type]))
                        ->mapWithKeys(fn($type) => [$type => collect($matrix[$type])->values()]);
                }
            }
        }

        return view('admin.subject.group.index', [
            'subject' => $subject,
            'batches' => $batchOptions,
            'selected_batch' => $selectedBatchId,
            'offered_courses_by_program_type' => $offeredCoursesByProgramType,
        ]);
    }

    function saveStudentGroupAllocation(Request $request, $id, $slug)
    {
        $subject = Subject::find($id);
        if (!$subject) {
            return redirect()->back()->with('error', 'Subject not found.');
        }

        if (!Schema::hasColumn('student_course_infos', 'allocation_group_id')) {
            return redirect()->back()->with('error', 'Missing column allocation_group_id. Please run migrations and try again.');
        }

        $request->validate([
            'batch' => 'required|integer|exists:batch_masters,id',
            'allocations' => 'required|array|min:1',
            'allocations.*.student_course_info_id' => 'required|integer|exists:student_course_infos,id',
            'allocations.*.allocation_group_id' => 'nullable|integer|min:1',
        ]);

        $batchId = (int) $request->batch;
        $allocations = collect($request->input('allocations', []))
            ->map(function ($row) {
                return [
                    'student_course_info_id' => (int) ($row['student_course_info_id'] ?? 0),
                    'allocation_group_id' => isset($row['allocation_group_id']) && $row['allocation_group_id'] !== '' ? (int) $row['allocation_group_id'] : null,
                ];
            })
            ->filter(fn($row) => $row['student_course_info_id'] > 0 && !empty($row['allocation_group_id']) && (int) $row['allocation_group_id'] > 0)
            ->unique('student_course_info_id')
            ->values();

        if ($allocations->isEmpty()) {
            return redirect()->back()->with('error', 'No valid student allocations were submitted.');
        }

        $combinations = SubjectHasStudentProgam::where('subject_id', (int) $subject->id)
            ->where('batch_id', $batchId)
            ->get(['id', 'student_program_id']);

        $combinationIds = $combinations->pluck('id')->filter()->map(fn($v) => (int) $v)->unique()->values();
        $programIds = $combinations->pluck('student_program_id')->filter()->map(fn($v) => (int) $v)->unique()->values();

        if ($combinationIds->isEmpty() || $programIds->isEmpty()) {
            return redirect()->back()->with('error', 'No subject program combinations found for the selected batch.');
        }

        $offeredMappings = ProgramWiseSemesterCourse::whereIn('program_combo_refid', $combinationIds->all())
            ->get(['course_id', 'semester'])
            ->filter(fn($row) => (int) ($row->course_id ?? 0) > 0 && (int) ($row->semester ?? 0) > 0)
            ->unique(fn($row) => ((int) $row->course_id) . '_' . ((int) $row->semester))
            ->values();

        if ($offeredMappings->isEmpty()) {
            return redirect()->back()->with('error', 'No offered courses found for the selected batch.');
        }

        $offeredKeySet = [];
        foreach ($offeredMappings as $map) {
            $offeredKeySet[(int) $map->course_id . '_' . (int) $map->semester] = true;
        }

        $sciIds = $allocations->pluck('student_course_info_id')->values()->all();

        $validRowsQuery = DB::table('student_course_infos as sci')
            ->join('student_masters as sm', 'sm.id', '=', 'sci.student_id')
            ->whereIn('sci.id', $sciIds)
            ->where('sm.batch', $batchId)
            ->whereIn('sm.new_program_id', $programIds->all())
            ->where('sm.is_deleted', 0)
            ->where('sm.is_left', 0)
            ->select('sci.id', 'sci.course_id', 'sci.semester');

        if (Schema::hasColumn('student_course_infos', 'is_deleted')) {
            $validRowsQuery->where('sci.is_deleted', 0);
        }

        $validRows = $validRowsQuery->get();

        $validSciIdSet = [];
        foreach ($validRows as $row) {
            $offeredKey = (int) ($row->course_id ?? 0) . '_' . (int) ($row->semester ?? 0);
            if (isset($offeredKeySet[$offeredKey])) {
                $validSciIdSet[(int) $row->id] = true;
            }
        }

        $updated = 0;
        foreach ($allocations as $row) {
            $sciId = (int) $row['student_course_info_id'];
            if (!isset($validSciIdSet[$sciId])) {
                continue;
            }

            StudentCourseInfo::where('id', $sciId)->update([
                'allocation_group_id' => (int) $row['allocation_group_id'],
            ]);
            $updated++;
        }

        if ($updated === 0) {
            return redirect()->back()->with('error', 'No valid group allocation rows were updated.');
        }

        return redirect()->back()->with('success', $updated . ' student group allocation(s) saved successfully.');
    }
}
