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
use App\Models\StudentMaster;
use App\Models\StudentAttendance;
use App\Models\StudentProgram;
use App\Models\Subject;
use App\Models\SubjectCombinationMaster;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasCombination;
use App\Models\SubjectHasDeptAdmin;
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
use App\Models\SyllabusHasFaculty;
use App\Models\CourseSeatAllocation;
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
use Illuminate\Support\Facades\Validator;
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
        $validator  =  Validator::make($request->all(), [
            'syllabus_id' => 'required',
            'weekday_id' => 'required',
            'hour_id' => 'required',
            'lecturehall_id' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'status' => false], 400);
        }

        $rec = new SubjectHasRoutine();
        $rec->syllabus_id = $request->syllabus_id;
        $rec->weekday_id = $request->weekday_id;
        $rec->hour_id = $request->hour_id;
        $rec->lecturehall_id = $request->lecturehall_id;
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

        // Backfill mappings for department-owned courses created without pivot rows.
        $departmentOwnedCourseIds = ProgramCourseMaster::where('department', $academicDeptId)
            ->where(function ($query) {
                $query->whereNull('is_deleted')->orWhere('is_deleted', 0);
            })
            ->pluck('id');

        foreach ($departmentOwnedCourseIds as $courseId) {
            // Respect prior deletions: if mapping exists in trash, do not recreate it.
            SubjectCourseMaster::withTrashed()->firstOrCreate([
                'subject_id' => $academicDeptId,
                'course_master_id' => $courseId,
            ]);
        }

        $courses =  SubjectCourseMaster::with([
            'courseMaster',
        ])->where('subject_id', $academicDeptId)
            ->whereHas('courseMaster', function ($query) {
                $query->where(function ($inner) {
                    $inner->whereNull('is_deleted')->orWhere('is_deleted', 0);
                });
            })->latest()
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
        return redirect()->back()->with('success', 'Course removed successfully');
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
        $data = StudentProgram::with(['programgroup', 'programtypemaster', 'combomap.combo1:id,title', 'combomap.combo2:id,title'])
            ->latest()->get()
            ->map(function ($program) {
                $program->student_count = StudentMaster::where('new_program_id', $program->id)->count();
                return $program;
            });

        return view('admin.subject.student-program-master', ['data' => $data]);
    }

    function addNewStudentProgram(Request $request)
    {
        $request->validate([
            'campus' => 'required',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'semester_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $rec = new StudentProgram();
        $rec->campus_id = $request->campus;
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
        $data = StudentProgram::findOrFail($id);
        $request->validate([
            'campus' => 'required',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'semester_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'program_type' => 'required',
        ]);

        $data->campus_id = $request->campus;
        $data->code = Str::upper($request->code);
        $data->name = $request->name;
        $data->description = $request->description;
        $data->semester_count = $request->semester_count;
        $data->program_type = $request->program_type;
        $data->save();

        if ($request->combo_id_1 || $request->combo_id_2) {
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
        $course = SubjectCourseMaster::with([
            'courseMaster.coursetypemaster',
            'courseMaster.csos.csosubunits.taxonomies.rbtmaster',
        ])->where('course_master_id', $courseId)->first();

        if (!$course) {
            return redirect()->back()->with('error', 'Course not found');
        }

        $objectives = PoHasCo::where('co_id', $courseId)->get();

        return view('admin.subject.course-objective', [
            'course' => $course,
            'objectives' => $objectives,
        ]);
    }

    function getCsoListForCourse($courseId)
    {
        $csos = CoHasCso::with(['csosubunits.taxomonylevel'])->where('co_id', $courseId)->get();

        if ($csos->isEmpty()) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        return response()->json($csos);
    }

    function createCourseSpecificObjective(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
            'course_id' => 'required',
            'lectures_needed' => 'required',
        ]);

        CoHasCso::create([
            'co_id' => $request->course_id,
            'title' => $request->title,
            'lectures_needed' => $request->lectures_needed,
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

        $validated = $request->validate([
            'title' => 'required',
            'lectures_needed' => 'required',
        ]);

        $cso->title = $request->title;
        $cso->lectures_needed = $request->lectures_needed;
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
        ])->where('subject_id', $id)->get();
        $data['id'] = $id;
        $data['slug'] = $request->slug;

        if (!empty($request->filter_batch)) {
            $syllabusData = SyllabusManager::with([
                'subject',
                'batch',
                'semester',
                'courseobjective',
                'cso.csosubunits.taxonomies.rbtmaster',
            ])->where('subject_id', $id)->where('batch_id', $request->filter_batch)->get();
        } else {
            $syllabusData = SyllabusManager::with([
                'subject',
                'batch',
                'semester',
                'courseobjective',
                'cso.csosubunits.taxonomies.rbtmaster',
            ])->where('subject_id', $id)->get();
        }


        // Organize data: Batch -> Semester -> Course -> CSOs
        $organized = [];
        foreach ($syllabusData as $syllabus) {
            $batchName = $syllabus->batch->batch_name ?? 'Unknown Batch';
            $semesterName = $syllabus->semester->title ?? 'Unknown Semester';
            $courseCode = $syllabus->courseobjective->course_code ?? 'N/A';
            $courseTitle = $syllabus->courseobjective->course_title ?? 'Unknown Course';
            $courseKey = $courseCode . ' - ' . $courseTitle;

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
            'seatAllocations' => $seatAllocations,
            'syllabuspdfs'   => $syllabuspdfs,
        ]);
    }

    function createSyllabus(Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'batch' => 'required',
            'semester' => 'required',
            'co_id' => 'required',
            'cso_id' => 'required',
            'cso_subunit' => 'required|array|min:1',

        ]);

        //save syllabus main table
        $rec = new SyllabusManager();
        $rec->subject_id = $request->subject_id;
        $rec->batch_id = $request->batch;
        $rec->semester_id = $request->semester;
        $rec->co_id = $request->co_id;
        $rec->cso_id = $request->cso_id;
        $rec->save();

        $syllabusId = $rec->id;

        //save syllabus subunit
        $cso_subunit = $request->cso_subunit;
        for ($i = 0; $i < count($cso_subunit); $i++) {
            SyllabusSubunit::create([
                'syllabus_manager_id' => $syllabusId,
                'unit_id' => $cso_subunit[$i],
                'is_completed' => 0, // default value for subunit
            ]);
        }

        return redirect()->back()->with('success', 'Syllabus Created');
    }

    function deleteSyllabusSubunit($id)
    {
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
                return redirect()->back()->with(
                    'error',
                    'Cannot remove this subunit — ' . implode(' and ', $reasons) . '. Please clear those first.'
                );
            }
        }

        $subunit->delete();
        return redirect()->back()->with('success', 'Subunit removed');
    }

    function deleteSyllabusCo($subjectId, $batchId, $semesterId, $coId)
    {
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
                'Cannot remove this course from the syllabus — ' . implode(' and ', $reasons) . '. Please clear those first before making changes.'
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

        return redirect()->back()->with('success', 'Course and all its objectives removed from syllabus');
    }

    function downloadSyllabusPdf(Request $request)
    {
        $id = $request->id;
        $subject = Subject::find($id);

        if (!empty($request->filter_batch)) {
            $syllabusData = SyllabusManager::with([
                'subject',
                'batch',
                'semester',
                'courseobjective',
                'cso',
                'syllabusSubunits.csoSubunit.taxomonylevel',
            ])->where('subject_id', $id)->where('batch_id', $request->filter_batch)->get();
        } else {
            $syllabusData = SyllabusManager::with([
                'subject',
                'batch',
                'semester',
                'courseobjective',
                'cso',
                'syllabusSubunits.csoSubunit.taxomonylevel',
            ])->where('subject_id', $id)->get();
        }

        // Organize data: Batch -> Semester -> Course -> CSOs
        $organized = [];
        foreach ($syllabusData as $syllabus) {
            $batchName = $syllabus->batch->batch_name ?? 'Unknown Batch';
            $semesterName = $syllabus->semester->title ?? 'Unknown Semester';
            $courseCode = $syllabus->courseobjective->course_code ?? 'N/A';
            $courseTitle = $syllabus->courseobjective->course_title ?? 'Unknown Course';
            $courseKey = $courseCode . ' - ' . $courseTitle;

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
}
