<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\AdmissionApplication;
use App\Models\BatchMaster;
use App\Models\Campus;
use App\Models\CognitiveLevelMaster;
use App\Models\CoHasCso;
use App\Models\CourseObjective;
use App\Models\CsoSubunit;
use App\Models\Department;
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
use App\Models\SyllabusHasFaculty;
use App\Models\CourseSeatAllocation;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\StdProgComboMap;
use App\Models\SubunitHasRbt;
use App\Models\SyllabusPdfUpload;
use App\Models\SyllabusManager;
use App\Models\SyllabusSubunit;
use App\Models\User;
use App\Models\UserHasRole;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class SubjectController extends Controller
{
    function index()
    {
        $data = Subject::with([
            'campusmaster'
        ])->latest()->get();
        return view('admin.master.subject', ['data' => $data]);
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
            ->with(['studentprograminfo', 'batchmaster'])
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
            'subject_id' => 'required',
            'batch_id' => 'required',
            'programs' => 'required|array|min:1',
            'program_type' => 'required',
            'total_seats' => 'required|integer|min:0',
        ]);

        $userId  = Auth::user()->id;
        if (!UserHasRole::where('user_id', $userId)->orWhere('role_name', 'dept-admin-erp')->orWhere('role_name', 'itcell')->exists()) {
            return redirect()->back()->with('info', 'Unauthorized to Access this Tool');
        }

        $programs = $request->programs;
        $subject_id = $request->subject_id;
        $data = Subject::find($subject_id);


        for ($i = 0; $i < count($programs); $i++) {

            $recordCheck = SubjectHasStudentProgam::where('subject_id', $subject_id)
                ->where('batch_id', $request->batch_id)
                ->where('student_program_id', $programs[$i])
                ->where('campus_id', $data->campus_id)
                ->where('program_type', $request->program_type)
                ->first();



            if ($recordCheck == null) {

                $departmentId =  StudentProgram::where('id', $programs[$i])->value('department');
                $subject = new SubjectHasStudentProgam();
                $subject->subject_id = $subject_id;
                $subject->batch_id = $request->batch_id;
                $subject->student_program_id = $programs[$i];
                $subject->campus_id = $data->campus_id;
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
            $activeBatch = $request->batch;
        } else {
            $activeBatch = BatchMaster::where('admission_active_batch', 1)->value('id');
        }

        $combinations = SubjectHasStudentProgam::where('subject_id', $subjectId)
            ->with(['studentprograminfo', 'batchmaster'])
            ->where('batch_id', $activeBatch)
            ->withCount(['studentmaster' => function ($query) use ($activeBatch) {
                $query->where('batch', $activeBatch);
            }])
            ->get();


        $programs = StudentProgram::where('campus_id', $subject->campus_id)->get();
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

        return view('admin.subject.department-dashboard', [
            'data' => $courseMaster,
            'students_count' => $studentsCount,
            'semesters_count' => $semestersCount,
            'batchWiseStudents' => $batchWiseStudents,
            'combinations' => $combinations,
            'programs' => $programs,
            'deptfaculties' => $faculties,
            'syllabusCount' => $syllabusCount,
            'upcomingActivities' => $upcomingActivities,
            'activityStats' => $activityStats,
            'subjectCombinationsGrouped' => $subjectCombinationsGrouped,
            'allSubjects' => $allSubjects,
            'allCampuses' => $allCampuses,
            'allBatches' => $batches,
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
                $query->where('batch', $activeBatch);
            }])
            ->value('studentmaster_count');

        //updating

        $request->validate([
            'total_seats' => 'required|integer|min:0',
        ]);


        $combination->total_seats = $request->total_seats;
        $combination->total_available_seats = $request->total_seats - $enrolledCount;
        $combination->save();

        return redirect()->back()->with('success', 'Combination Updated');
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
            $comboLabel = 'N/A';
            if ($data->programtypemaster && $data->programtypemaster->name === 'UGC') {
                $comboLabel = ($data->combomap->combo1->title ?? 'Unknown') . ' - ' . ($data->combomap->combo2->title ?? 'Unknown');
            } elseif ($data->programtypemaster && $data->programtypemaster->name !== 'UGC') {
                $comboLabel = 'N/A for AICTE';
            }

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
            'batch' => 'required',
            'course_code' => 'required|string|max:100',
            'course_title' => 'required|string|max:255',
            'course_type' => 'required',
            'internal' => 'required|numeric|min:0',
            'external' => 'required|numeric|min:0',
            'credits' => 'required|numeric|min:0',
        ]);

        $rec = new ProgramCourseMaster();
        $rec->academic_year = $request->batch;
        $rec->course_code = Str::upper($request->course_code);
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

    function getCsoListForCourse(Request $request, $courseId)
    {
        $defaultShift = $this->getDefaultShiftSlug();
        $query = CoHasCso::with(['csosubunits.taxomonylevel'])
            ->where('co_id', $courseId)
            ->where(function ($q) use ($defaultShift) {
                $q->where('shift', $defaultShift)
                    ->orWhereNull('shift');
            });

        $csos = $this->dedupeCsosByTitle($query->orderBy('id')->get());

        if ($csos->isEmpty()) {
            return response()->json(['error' => 'Course not found'], 404);
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
            'course_code' => 'required|string|max:255',
            'course_title' => 'required|string|max:255',
            'course_type' => 'required',
            'paper_type' => 'required',
        ]);

        ProgramCourseMaster::where('id', $id)->update([
            'course_code' => Str::upper($request->course_code),
            'course_title' => $request->course_title,
            'course_type' => $request->course_type,
            'credits' => $request->credits,
            'internal' => $request->internal,
            'external' => $request->external,
            'total' => $request->internal + $request->external,
            'total_alloted_hours' => $request->total_alloted_hours,
            'paper_type_id' => $request->paper_type,
        ]);

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
        $batches = BatchMaster::all();
        $semesters = Semester::all();
        $cos =  SubjectCourseMaster::with([
            'courseMaster.coursetypemaster'
        ])
            ->where('subject_id', $id)
            ->get()
            ->unique('course_master_id')
            ->values();

        $mappedCourseIds = $cos
            ->pluck('course_master_id')
            ->filter()
            ->map(fn($courseId) => (int) $courseId)
            ->unique()
            ->values();

        $data['id'] = $id;
        $data['slug'] = $request->slug;
        $subjectUsesShifts = $this->subjectUsesShifts($id);

        $syllabusQuery = SyllabusManager::with([
            'subject',
            'batch',
            'semester',
            'courseobjective',
            'cso.csosubunits.taxonomies.rbtmaster',
        ])->where('subject_id', $id);

        if (!empty($request->filter_batch)) {
            $syllabusQuery->where('batch_id', $request->filter_batch);
        }

        if ($subjectUsesShifts && !empty($request->filter_shift) && $this->isKnownShift($request->filter_shift)) {
            $syllabusQuery->where('shift', $request->filter_shift);
        }

        if ($mappedCourseIds->isNotEmpty()) {
            $syllabusQuery->whereIn('co_id', $mappedCourseIds->all());
        } else {
            $syllabusQuery->whereRaw('1 = 0');
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
            $courseKey = $courseCode . ' - ' . $courseTitle . ' [' . $shiftLabel . ']';

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

        $data['organized_syllabus'] = $organized;
        $shiftOptions = ShiftMaster::where('is_active', 1)
            ->orderBy('sort_order')
            ->get(['title', 'slug']);

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
            'data'           => $data,
            'shiftOptions'   => $shiftOptions,
            'subjectUsesShifts' => $subjectUsesShifts,
            'seatAllocations' => $seatAllocations,
            'syllabuspdfs'   => $syllabuspdfs,
        ]);
    }

    function createSyllabus(Request $request)
    {
        $allowedShifts = $this->getShiftSlugs();
        $usesShifts = $this->subjectUsesShifts((int) $request->subject_id);
        $request->validate([
            'subject_id' => 'required',
            'batch' => 'required',
            'semester' => 'required',
            'shift' => ['nullable', Rule::in($allowedShifts)],
            'create_all_shifts' => 'nullable|boolean',
            'co_id' => 'required',
            'cso_id' => 'required',
            'cso_subunit' => 'required|array|min:1',
            'status' => 'required|in:draft,published',

        ]);

        // save syllabus main table (single or all shifts)
        $defaultShift = $this->getDefaultShiftSlug();
        $targetShifts = [$defaultShift];
        if ($usesShifts) {
            if ($request->boolean('create_all_shifts')) {
                $targetShifts = collect($this->getShiftSlugs())
                    ->reject(fn($shift) => $shift === $defaultShift)
                    ->values()
                    ->all();

                if (empty($targetShifts)) {
                    $targetShifts = [$defaultShift];
                }
            } else {
                $targetShifts = [$request->shift ?? $defaultShift];
            }
        }

        $createdCount = 0;
        $updatedCount = 0;
        $status = $request->status ?? 'draft';

        // save syllabus subunit
        $cso_subunit = $request->cso_subunit;
        foreach ($targetShifts as $shiftSlug) {
            $rec = SyllabusManager::updateOrCreate([
                'subject_id' => $request->subject_id,
                'batch_id' => $request->batch,
                'semester_id' => $request->semester,
                'shift' => $shiftSlug,
                'co_id' => $request->co_id,
                'cso_id' => $request->cso_id,
            ], [
                'status' => $status,
            ]);

            if ($rec->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }

            for ($i = 0; $i < count($cso_subunit); $i++) {
                SyllabusSubunit::firstOrCreate([
                    'syllabus_manager_id' => $rec->id,
                    'unit_id' => $cso_subunit[$i],
                ], [
                    'is_completed' => 0,
                ]);
            }
        }

        if ($usesShifts && $request->boolean('create_all_shifts')) {
            return redirect()->back()->with('success', 'Syllabus saved for all active shifts with status: ' . ucfirst($status));
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

        $query = SyllabusManager::where('subject_id', $subjectId)
            ->where('batch_id', $batchId)
            ->where('semester_id', $semesterId)
            ->where('co_id', $coId);

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
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

    function deleteSyllabusCo($subjectId, $batchId, $semesterId, $coId)
    {
        $isJsonRequest = request()->expectsJson() || request()->ajax();
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
            ->where('co_id', $coId)
            ->get();

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

        if ($subjectUsesShifts && !empty($request->filter_shift) && $this->isKnownShift($request->filter_shift)) {
            $syllabusQuery->where('shift', $request->filter_shift);
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
            $courseKey = $courseCode . ' - ' . $courseTitle . ' [' . $shiftLabel . ']';

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

    function allStudents(Request $request)
    {
        $userId    = Auth::user()->id;
        $subjectId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
        $subject   = Subject::find($subjectId);

        if (!$subject) {
            return redirect()->route('department.dashboard')->with('info', 'Subject not found.');
        }

        $batches = BatchMaster::all();

        $query = StudentMaster::where('is_left', '0')->where('is_deleted', '0')->with(['batchmaster', 'campusmaster', 'stdprogramenrolled']);

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
            'programgroup.programInfo',
            'feepayment.feepaymentinfo:id,quarter_title',
            'feepayment.gatewaytype',
        ])->firstOrFail();

        $studentId = $data->id;

        // Courses with semester and type
        $studentCourses = StudentCourseInfo::with([
            'coursemaster.semestermaster:id,title',
            'coursemaster.coursetypemaster:id,title,description',
        ])->where('student_id', $studentId)->get();

        $coursesBySemester = $studentCourses
            ->sortBy(fn($c) => $c->coursemaster?->semester_id ?? 999)
            ->groupBy(fn($c) => $c->coursemaster?->semestermaster?->title ?? ('Sem ' . ($c->semester ?? '?')));

        // Timetable
        $timetable = SubjectHasRoutine::where('batch_id', $data->batch)
            ->with([
                'weekdaymaster:id,title',
                'hourmaster:id,title',
                'lecturehallmaster:id,title',
                'faculty:id,FIRST_NAME,LAST_NAME',
                'coursemaster:id,course_title,course_code',
            ])
            ->orderBy('weekday_id')
            ->orderBy('hour_id')
            ->get();

        $timetableByDay = $timetable->groupBy(fn($r) => $r->weekdaymaster->title ?? 'Unknown');

        // Attendance per course
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

        // Internal marks
        $internalMarks = InterMark::where('student_id', $studentId)
            ->with(['course:id,course_title,course_code', 'semester:id,title'])
            ->orderBy('semester')
            ->get();

        // Exam results
        $examStudent       = ExamStudent::where('erp_student_id', $studentId)->first();
        $examResults       = collect();
        $resultsBySemester = collect();
        if ($examStudent) {
            $examResults = Result::where('exam_student_id', $examStudent->id)
                ->where('is_published', true)
                ->with(['examSession', 'resultSubjects'])
                ->orderBy('exam_session_id')
                ->get();

            foreach ($examResults as $result) {
                $semKey = 'Semester ' . ($result->examSession?->semester ?? '?') . ' — ' . ($result->examSession?->academic_year ?? '');
                $resultsBySemester[$semKey] = [
                    'result'    => $result,
                    'qualified' => $result->resultSubjects->where('result_status', 'pass')->values(),
                    'backlog'   => $result->resultSubjects->where('result_status', '!=', 'pass')->values(),
                ];
            }
        }

        // Exam registrations
        $examRegistrations = Registration::where('erp_student_id', $studentId)
            ->with(['examSession', 'registrationSubjects.examSubject.master'])
            ->orderByDesc('registered_at')
            ->get();

        return view('student.profile', [
            'data'               => $data,
            'studentCourses'     => $studentCourses,
            'coursesBySemester'  => $coursesBySemester,
            'timetableByDay'     => $timetableByDay,
            'attendanceSummary'  => $attendanceSummary,
            'internalMarks'      => $internalMarks,
            'examResults'        => $examResults,
            'resultsBySemester'  => $resultsBySemester,
            'examStudent'        => $examStudent,
            'examRegistrations'  => $examRegistrations,
            'latestRegistration' => $examRegistrations->first(),
            'dept_view'          => true,
        ]);
    }

    function deptFacultyList($subjectId)
    {
        $faculties = SubjectFacultyMaster::with('faculty')->where('subject_id', $subjectId)->get();
        $subject = Subject::find($subjectId);
        return view('admin.department.faculty.index', [
            'data' => $faculties,
            'subject' => $subject,
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
        SubunitHasRbt::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Taxonomy level removed from subunit');
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
        Subject::where('id', $id)->update([
            'has_shift_delivery' =>  $data->has_shift_delivery === 1 ? 0 : 1,
        ]);

        return redirect()->back()->with('success', 'Subject shift mode updated');
    }

    private function subjectUsesShifts(?int $subjectId): bool
    {
        if (empty($subjectId)) {
            return false;
        }

        return Subject::where('id', $subjectId)->value('has_shift_delivery') == 1;
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

        $comboBoundary = $this->getProgrammeBoundarySubjectIds($data);

        $coursesBySemester = ProgramWiseSemesterCourse::with([
            'semestermaster:id,title',
            'programinfo:id,course_code,course_title,course_type,department',
            'programinfo.coursetypemaster:id,title'
        ])
            ->where('program_combo_refid', $id)
            ->orderBy('semester')
            ->when(
                Schema::hasColumn($this->getCurriculumEngineTable(), 'display_order'),
                fn($query) => $query->orderBy('display_order')->orderBy('id'),
                fn($query) => $query->orderBy('id')
            )
            ->get()
            ->groupBy('semester');

        $publishedCoursesBySemester = $this->getPublishedCoursesBySemesterForCombination($data);
        $selectedSemester = (int) request('semester');
        $generatedCourses = $selectedSemester > 0
            ? collect($publishedCoursesBySemester[(string) $selectedSemester] ?? [])
            : collect();


        return view('admin.subject.curriculam-builder', [
            'data' => $data,
            'comboBoundary' => $comboBoundary,
            'coursesBySemester' => $coursesBySemester,
            'publishedCoursesBySemester' => $publishedCoursesBySemester,
            'selectedSemester' => $selectedSemester,
            'generatedCourses' => $generatedCourses,

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
        $semester = $request->semester;
        $batch = $request->batch;

        $combo1OfferedCourses =   SyllabusManager::with(['courseobjective.coursetypemaster', 'subject:id,title,code'])
            ->where('subject_id', $combo1SubjectId)->where('batch_id', $batch)->where('semester_id', $semester)
            ->where('status', 'published')->get();

        $combo2OfferedCourses =   SyllabusManager::with(['courseobjective.coursetypemaster', 'subject:id,title,code'])
            ->where('subject_id', $combo2SubjectId)->where('batch_id', $batch)->where('semester_id', $semester)
            ->where('status', 'published')->get();

        $mapCourses = function ($syllabi, string $source) {
            return collect($syllabi)
                ->map(function ($syllabus) use ($source) {
                    $course = $syllabus->courseobjective;
                    if (!$course) {
                        return null;
                    }

                    $courseTypeTitle = strtoupper(trim((string) optional($course->coursetypemaster)->title));
                    $courseType = $courseTypeTitle !== '' ? $courseTypeTitle : 'NA';

                    // MAJ from combo1 should be CORE A.
                    if ($courseTypeTitle === 'MAJ') {
                        $courseType = $source === 'combo1' ? 'CORE A' : 'CORE B';
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
        $hasDisplayOrderColumn = Schema::hasColumn($curriculumTable, 'display_order');
        $hasIsActiveColumn = Schema::hasColumn($curriculumTable, 'is_active');

        if (($academicPathwayId !== null && !$hasAcademicPathwayColumn) || ($degreeTrackId !== null && !$hasDegreeTrackColumn)) {
            return $respond(false, 'Pathway/Track columns are missing in curriculum table. Please run latest migrations and try again.', 422);
        }

        $combination = SubjectHasStudentProgam::with([
            'batchmaster:id,batch_name',
            'studentprograminfo:id,code,name',
            'combomap:id,student_program_id,combo_id_1,combo_id_2',
        ])->find($refid);
        if (!$combination) {
            return $respond(false, 'Program mapping not found.', 404);
        }

        $eligibleCourseIds = $this->getEligiblePublishedCourseIdsForSemester($combination, (int) $semester);

        $typedSelections = collect((array) $request->selected_courses)
            ->map(fn($courseId) => (int) $courseId)
            ->unique()
            ->map(function ($courseId) use ($request) {
                $rawType = data_get($request->input('course_type_map', []), (string) $courseId);
                $type = strtoupper((string) $rawType);
                $rawDeliveryCategory = data_get($request->input('delivery_category_map', []), (string) $courseId);
                $deliveryCategory = $this->normalizeDeliveryCategoryInput($rawDeliveryCategory);
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

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($typedSelections as $index => $selection) {
            $courseId = (int) $selection['course_id'];
            $courseType = $selection['course_type'];

            $existingQuery = ProgramWiseSemesterCourse::where('program_combo_refid', $refid)
                ->where('batch', $batch)
                ->where('semester', $semester)
                ->where('course_id', $courseId);

            if ($hasAcademicPathwayColumn) {
                $existingQuery->where('academic_pathway_id', $academicPathwayId);
            }

            if ($hasDegreeTrackColumn) {
                $existingQuery->where('degree_track_id', $degreeTrackId);
            }

            $existing = $existingQuery->first();

            $courseInfo = ProgramCourseMaster::with('coursetypemaster:id,title')->find($courseId);
            $offeringDeptId = (int) ($courseInfo->department ?? 0);
            $deliveryCategory = $selection['delivery_category'] ?? null;
            if (empty($deliveryCategory)) {
                $deliveryCategory = $this->deriveDeliveryCategory($combination, $courseInfo);
            }

            if ($existing) {
                $existing->course_type = $courseType;
                if ($hasDeliveryCategoryColumn) {
                    $existing->delivery_category = $deliveryCategory;
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

            if ($hasOfferingDeptColumn) {
                $createData['offering_dept'] = $offeringDeptId > 0 ? $offeringDeptId : null;
            }

            if ($hasAcademicPathwayColumn) {
                $createData['academic_pathway_id'] = $academicPathwayId;
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

        $message = 'Curriculum mapping saved.';
        if ($createdCount > 0 && $updatedCount > 0) {
            $message = 'Curriculum mapping created and updated.';
        } elseif ($updatedCount > 0 && $createdCount === 0) {
            $message = 'Curriculum mapping updated.';
        }

        return $respond(true, $message, 200, [
            'created' => $createdCount,
            'updated' => $updatedCount,
        ]);
    }

    private function normalizeDeliveryCategoryInput(?string $value): ?string
    {
        $normalized = strtoupper(trim((string) $value));
        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['CORE A', 'CORE-A', 'COREA', 'MAJOR_COMBO1'], true)) {
            return ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
        }

        if (in_array($normalized, ['CORE B', 'CORE-B', 'COREB', 'MAJOR_COMBO2'], true)) {
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
        $courseType = strtoupper((string) $request->course_type);
        $academicPathwayId = $request->filled('academic_pathway_id') ? (int) $request->academic_pathway_id : null;
        $degreeTrackId = $request->filled('degree_track_id') ? (int) $request->degree_track_id : null;
        $curriculumTable = $this->getCurriculumEngineTable();
        $hasAcademicPathwayColumn = Schema::hasColumn($curriculumTable, 'academic_pathway_id');
        $hasDegreeTrackColumn = Schema::hasColumn($curriculumTable, 'degree_track_id');
        $hasOfferingDeptColumn = Schema::hasColumn($curriculumTable, 'offering_dept');
        $hasDeliveryCategoryColumn = Schema::hasColumn($curriculumTable, 'delivery_category');
        $hasDisplayOrderColumn = Schema::hasColumn($curriculumTable, 'display_order');
        $hasIsActiveColumn = Schema::hasColumn($curriculumTable, 'is_active');

        if (($academicPathwayId !== null && !$hasAcademicPathwayColumn) || ($degreeTrackId !== null && !$hasDegreeTrackColumn)) {
            return redirect()->back()->with('error', 'Pathway/Track columns are missing in curriculum table. Please run latest migrations and try again.');
        }
        $combination = SubjectHasStudentProgam::with('combomap:id,student_program_id,combo_id_1,combo_id_2')->find($mapping->program_combo_refid);

        if (!$combination) {
            return redirect()->back()->with('error', 'Program mapping not found.');
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
        $combo1DepartmentId = $this->resolveSubjectToDepartmentId($combo1SubjectId);
        $combo2DepartmentId = $this->resolveSubjectToDepartmentId($combo2SubjectId);
        $courseDepartmentId = (int) ($courseInfo->department ?? 0);
        $courseTypeTitle = strtoupper(trim((string) optional($courseInfo->coursetypemaster)->title));

        if ($courseTypeTitle === 'MDC') {
            return ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE;
        }

        if ($courseTypeTitle === 'MAJ') {
            if ($combo1DepartmentId > 0 && $courseDepartmentId === $combo1DepartmentId) {
                return ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
            }

            if ($combo2DepartmentId > 0 && $courseDepartmentId === $combo2DepartmentId) {
                return ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2;
            }

            return ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
        }

        return ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
    }

    private function resolveSubjectToDepartmentId(int $subjectId): int
    {
        if ($subjectId <= 0) {
            return 0;
        }

        if (!Schema::hasTable('subjects') || !Schema::hasTable('department_masters')) {
            return $subjectId;
        }

        $subject = Subject::query()
            ->select(['id', 'code', 'title', 'campus_id'])
            ->find($subjectId);

        if (!$subject) {
            return $subjectId;
        }

        if (Schema::hasColumn('subjects', 'main_dept_id')) {
            $directDepartmentId = (int) ($subject->main_dept_id ?? 0);
            if ($directDepartmentId > 0) {
                return $directDepartmentId;
            }
        }

        $departmentQuery = DB::table('department_masters');

        if (Schema::hasColumn('department_masters', 'campus_id') && Schema::hasColumn('subjects', 'campus_id')) {
            $departmentQuery->where('campus_id', (int) ($subject->campus_id ?? 0));
        }

        $subjectCode = strtoupper(trim((string) ($subject->code ?? '')));
        if ($subjectCode !== '') {
            if (Schema::hasColumn('department_masters', 'department_code')) {
                $matchedByDeptCode = (int) (clone $departmentQuery)
                    ->whereRaw('UPPER(TRIM(department_code)) = ?', [$subjectCode])
                    ->value('id');

                if ($matchedByDeptCode > 0) {
                    return $matchedByDeptCode;
                }
            }

            if (Schema::hasColumn('department_masters', 'code')) {
                $matchedByCode = (int) (clone $departmentQuery)
                    ->whereRaw('UPPER(TRIM(code)) = ?', [$subjectCode])
                    ->value('id');

                if ($matchedByCode > 0) {
                    return $matchedByCode;
                }
            }
        }

        $subjectTitle = strtoupper(trim((string) ($subject->title ?? '')));
        if ($subjectTitle !== '') {
            if (Schema::hasColumn('department_masters', 'name')) {
                $matchedByName = (int) (clone $departmentQuery)
                    ->whereRaw('UPPER(TRIM(name)) = ?', [$subjectTitle])
                    ->value('id');

                if ($matchedByName > 0) {
                    return $matchedByName;
                }
            }

            if (Schema::hasColumn('department_masters', 'title')) {
                $matchedByTitle = (int) (clone $departmentQuery)
                    ->whereRaw('UPPER(TRIM(title)) = ?', [$subjectTitle])
                    ->value('id');

                if ($matchedByTitle > 0) {
                    return $matchedByTitle;
                }
            }
        }

        // Last fallback for legacy data where ids happened to align.
        return $subjectId;
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

        $query = SyllabusManager::with([
            'courseobjective:id,course_code,course_title,course_type,department',
            'courseobjective.coursetypemaster:id,title',
            'courseobjective.departmentmaster:id,name,department_code',
        ])
            ->where('batch_id', $combination->batch_id);

        if (Schema::hasColumn('syllabus_managers', 'status')) {
            $query->where('status', 'published');
        }

        $publishedSyllabus = $query->get();

        $bySemester = [];

        foreach ($publishedSyllabus as $syllabus) {
            $course = $syllabus->courseobjective;
            if (!$course) {
                continue;
            }

            $courseTypeTitle = strtoupper(trim((string) optional($course->coursetypemaster)->title));

            $deliveryCategory = $this->deriveDeliveryCategory($combination, $course, $boundary);
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
                'source_subject_id' => (int) ($course->department ?? 0),
                'source_subject' => (string) (optional($course->departmentmaster)->title ?? optional($course->departmentmaster)->name ?? 'NA'),
                'source_subject_code' => (string) (optional($course->departmentmaster)->code ?? ''),
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
    }

    private function resolveCurriculumCourseType(array $course): string
    {
        $courseTypeTitle = strtoupper(trim((string) ($course['course_type_title'] ?? '')));
        $deliveryCategory = strtoupper(trim((string) ($course['delivery_category'] ?? '')));

        if ($courseTypeTitle === 'MAJ') {
            if (in_array($deliveryCategory, ['CORE-A', 'COREA', 'MAJOR_COMBO1'], true)) {
                return 'COREA';
            }

            if (in_array($deliveryCategory, ['CORE-B', 'COREB', 'MAJOR_COMBO2'], true)) {
                return 'COREB';
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
            $alreadyEnrolled = StudentCourseInfo::where('student_id', $student->id)
                ->where('course_id', $mapping->course_id)
                ->where('semester', $mapping->semester)
                ->where('academic_year', $academicYear)
                ->exists();

            if ($alreadyEnrolled) {
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
        return StudentMaster::where('new_program_id', $combination->student_program_id)
            ->where('batch', $combination->batch_id)
            ->where('campus_id', $combination->campus_id)
            ->where('is_deleted', 0)
            ->where('is_left', 0)
            ->get(['id', 'campus_id']);
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
            ])
            ->latest()
            ->get();

        return view('admin.subject.teaching.index', [
            'subject' => $subjectInfo,
            'courses' => $courses,
            'faculties' => $faculties,
            'assignments' => $assignments,
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

        $validated = $request->validate([
            'course_id' => 'required|integer|exists:program_course_masters,id',
            'faculty_id' => 'required|integer|exists:faculties,id',
            'delivery_type' => 'required|string|max:100',
            'status' => 'required|in:0,1',
            'room' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $isCourseMapped = SubjectCourseMaster::where('subject_id', $subject->id)
            ->where('course_master_id', $validated['course_id'])
            ->exists();

        if (!$isCourseMapped) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Selected course is not mapped to this department.'], 422);
            }
            return redirect()->back()->with('error', 'Selected course is not mapped to this department.');
        }

        $isFacultyMapped = SubjectFacultyMaster::where('subject_id', $subject->id)
            ->where('faculty_id', $validated['faculty_id'])
            ->exists();

        if (!$isFacultyMapped) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Selected faculty is not mapped to this department.'], 422);
            }
            return redirect()->back()->with('error', 'Selected faculty is not mapped to this department.');
        }

        $duplicateExists = TeachingAssignment::where('subject_id', $subject->id)
            ->where('course_id', $validated['course_id'])
            ->where('faculty_id', $validated['faculty_id'])
            ->where('delivery_type', $validated['delivery_type'])
            ->exists();

        if ($duplicateExists) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Duplicate entry: this course, faculty and delivery type already exists.'], 422);
            }
            return redirect()->back()->with('error', 'Duplicate entry: this course, faculty and delivery type already exists.');
        }

        $lastGroup = TeachingAssignment::where('subject_id', $subject->id)
            ->where('course_id', $validated['course_id'])
            ->where('delivery_type', $validated['delivery_type'])
            ->max('allocation_group');

        $nextAllocationGroup = ((int) $lastGroup) + 1;

        $assignment = TeachingAssignment::create([
            'subject_id' => $subject->id,
            'course_id' => $validated['course_id'],
            'delivery_type' => $validated['delivery_type'],
            'faculty_id' => $validated['faculty_id'],
            'allocation_group' => $nextAllocationGroup,
            'is_active' => (int) $validated['status'],
            'room' => $validated['room'] ?? '',
            'remarks' => $validated['remarks'] ?? '',
        ]);

        $assignment->load([
            'course:id,course_code,course_title',
            'faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
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

        $validated = $request->validate([
            'course_id' => 'required|integer|exists:program_course_masters,id',
            'faculty_id' => 'required|integer|exists:faculties,id',
            'delivery_type' => 'required|string|max:100',
            'status' => 'required|in:0,1',
            'room' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $isCourseMapped = SubjectCourseMaster::where('subject_id', $assignment->subject_id)
            ->where('course_master_id', $validated['course_id'])
            ->exists();

        if (!$isCourseMapped) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Selected course is not mapped to this department.'], 422);
            }
            return redirect()->back()->with('error', 'Selected course is not mapped to this department.');
        }

        $isFacultyMapped = SubjectFacultyMaster::where('subject_id', $assignment->subject_id)
            ->where('faculty_id', $validated['faculty_id'])
            ->exists();

        if (!$isFacultyMapped) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Selected faculty is not mapped to this department.'], 422);
            }
            return redirect()->back()->with('error', 'Selected faculty is not mapped to this department.');
        }

        $duplicateExists = TeachingAssignment::where('subject_id', $assignment->subject_id)
            ->where('course_id', $validated['course_id'])
            ->where('faculty_id', $validated['faculty_id'])
            ->where('delivery_type', $validated['delivery_type'])
            ->where('id', '!=', $assignment->id)
            ->exists();

        if ($duplicateExists) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Duplicate entry: this course, faculty and delivery type already exists.'], 422);
            }
            return redirect()->back()->with('error', 'Duplicate entry: this course, faculty and delivery type already exists.');
        }

        $combinationChanged =
            (int) $assignment->course_id !== (int) $validated['course_id'] ||
            (int) $assignment->faculty_id !== (int) $validated['faculty_id'] ||
            (string) $assignment->delivery_type !== (string) $validated['delivery_type'];

        if ($combinationChanged) {
            $lastGroup = TeachingAssignment::where('subject_id', $assignment->subject_id)
                ->where('course_id', $validated['course_id'])
                ->where('delivery_type', $validated['delivery_type'])
                ->where('id', '!=', $assignment->id)
                ->max('allocation_group');
            $assignment->allocation_group = ((int) $lastGroup) + 1;
        }

        $assignment->course_id = $validated['course_id'];
        $assignment->faculty_id = $validated['faculty_id'];
        $assignment->delivery_type = $validated['delivery_type'];
        $assignment->is_active = (int) $validated['status'];
        $assignment->room = $validated['room'] ?? '';
        $assignment->remarks = $validated['remarks'] ?? '';
        $assignment->save();

        $assignment->load([
            'course:id,course_code,course_title',
            'faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
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

    private function serializeTeachingAssignment(TeachingAssignment $assignment): array
    {
        return [
            'id' => $assignment->id,
            'course_text' => trim(($assignment->course->course_code ?? '-') . ' - ' . ($assignment->course->course_title ?? '-')),
            'faculty_text' => trim(($assignment->faculty->USER_CODE ?? '-') . ' - ' . ($assignment->faculty->FIRST_NAME ?? '-') . ' ' . ($assignment->faculty->LAST_NAME ?? '')),
            'delivery_type' => $assignment->delivery_type,
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
        ];
    }
}
